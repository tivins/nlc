#!/usr/local/bin/php
<?php


use \parse\Source;
use \parse\Expression;


include __dir__ . '/src/loader.php';
Msg::set_store_history(true);
Output::set(new output_TTY);


//# validation des élements d'entrée:
//-----------------------------------
function usage() {
    $prog = $GLOBALS['argv'] ? reset($GLOBALS['argv']) : 'command';
    Msg::info("Usage: $prog [options] -f <file_or_directory>");
    Msg::info("");
    Msg::info("  -f <file or folder>  Required. A file or a directory.");
    Msg::info("  -r, --recursive      Recursive. Only if -f is a directory.");
    Msg::info("  -d                   Dump. Display the dump of the token tree at the end of the test.");
    Msg::info("  -h --help            Display the manual page.");
}
$options = getopt("f:drh", ['help', 'recursive']) ;
if (isset($options['h']) || isset($options['help'])) { usage(); exit(Msg::success); }
if (!isset($options['f'])) { usage(); Msg::fatal("Argument -f is required"); }
if (isset($options['recursive'])) $options['r'] = 1;
$folder = trim($options['f']);

Msg::info("Running tests...");

//# test des fichiers:
//--------------------
$results = [];
if (is_file($folder)) $files = [$folder];
else {
    if (isset($options['r']))
    {
        $Directory = new RecursiveDirectoryIterator($folder);
        $Iterator = new RecursiveIteratorIterator($Directory);
        $files_it = new RegexIterator($Iterator, '/.+\.n$/i', RecursiveRegexIterator::GET_MATCH);
        $files=[]; foreach ($files_it as $file) $files[] = reset($file);
        natcasesort($files);
    }
    else {
        $files = glob(rtrim($folder,'/').'/*.n');

    }
}

if (empty($files)) {
    Msg::fatal("No files to test");
}

foreach($files as $file) {
    $success = false ;
    test_file($file, $options, $success);
    Msg::test($success);
    $results[$file] = $success;
}


//# Analyse résultats:
//--------------------
$nb_tests = count($files);
$nb_success = array_sum($results);
$nb_fail = $nb_tests - $nb_success;
$score = number_format($nb_success/$nb_tests*100, 2);

$history = Msg::get_history();
$num_histo = count($history);
$histo_types = array_count_values(array_column($history,'type'));
arsort($histo_types);


//# Affichage résultats:
//----------------------
echo "\n";
if ($score==100) { echo "  ".Msg::fmt_success("Full success")."\n"; }
else { echo "  ".Msg::fmt_error("Not perfect.")."\n"; }
echo "  Score: ".($score==100?$score.'%':Msg::fmt_texterror($score.'%')).", $nb_tests tests.\n  ";
echo Msg::fmt_success(" $nb_success ")." success ";
if ($nb_fail) echo Msg::fmt_error(" $nb_fail ")." failed.";
echo "\n\n";


echo "  Log history: $num_histo.\n";
foreach ($histo_types as $type => $count) {
    echo "  $type: $count (".round($count/$num_histo*100)."%)\n";
}
echo"\n";

//# Fonction de test:
//-------------------
function test_file($file, $options, &$success) {

    Msg::info("Testing: $file");

    //# Chargement du fichier de règles et résultats:
    //-----------------------------------------------
    $json_file = str_replace('.n', '.r', $file) ;
    if (!file_exists($json_file)){
        return Msg::error("file '$json_file' doesn't exists.");
    }
    $exp_res = json_decode(file_get_contents($json_file));
    if (is_null($exp_res)) {
        switch (json_last_error()) {
            case JSON_ERROR_NONE: $err = 'No errors'; break;
            case JSON_ERROR_DEPTH: $err = 'Maximum stack depth exceeded'; break;
            case JSON_ERROR_STATE_MISMATCH: $err = 'Underflow or the modes mismatch'; break;
            case JSON_ERROR_CTRL_CHAR: $err = 'Unexpected control character found'; break;
            case JSON_ERROR_SYNTAX: $err = 'Syntax error, malformed JSON'; break;
            case JSON_ERROR_UTF8: $err = 'Malformed UTF-8 characters, possibly incorrectly encoded'; break;
            default: $err = 'Unknown error'; break;
        }
        return Msg::error("Cannot load result file '$json_file' (err: $err).");
    }

    if (isset($exp_res->info)) {
        if (isset($exp_res->info->title))       Msg::info("Title:   ".Msg::fmt_high($exp_res->info->title)) ;
            if (isset($exp_res->info->created)) Msg::info("Created: ".date('j M Y',strtotime($exp_res->info->created))) ;
    }

    $schema = array_map(function($item) {
        if (isset($item->type)) $item->type = parse\Token::get_type_from_name($item->type);
        return (array) $item;
    }, $exp_res->expected_results->schema);


    //# Chargement, parsing du fichier test:
    //--------------------------------------
    $ch_src = Util::chrono_start();
    $source = Source::from_file($file) ;
    if ($source === false) {
        return Msg::error("Failed to get a valid Source object.");
    }
    Msg::time("Source::from_file()", Util::chrono_duration($ch_src));

    $result = false;
    try {

        $ch = Util::chrono_start();
        if (! $source->tokenize()) {
            return Msg::error("Cannot tokenize()");
        }
        Msg::time('$source->tokenize()', Util::chrono_duration($ch));

        $ch = Util::chrono_start();
        if (! $source->treefy()) {
            if (isset($exp_res->expected_results->statuses->treefy) &&
                $exp_res->expected_results->statuses->treefy == false) {
                Msg::info("Cannot treefy(), as expected.");
                $success = true;
                return Msg::success;
            }
            return Msg::error("Cannot treefy()");
        }
        Msg::time('$source->treefy()', Util::chrono_duration($ch));

        //if (isset($options['d'])) $source->dump();
        Expression::parse($source->root);

        if ($exp_res->rules->final_trim) $source->trim_tokens();

        if (isset($options['d'])) $source->dump();

        if ($source->is_empty()) {
            Msg::warn('source is empty');
            return false;
        }

        $result = parse\Validator::validate_schema($source->root->next, $schema) ;
    }
    catch(Exception $ex) {
        Msg::error($ex->getMessage());
    }
    $success = $result !== false;
    return Msg::success;
}

