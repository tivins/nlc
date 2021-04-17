#!/usr/bin/php
<?php

include __dir__ . '/src/loader.php';
Output::set(new Output_TTY);


// --------- Gestion des options du programme

$options = getopt('o:f:d', ['debug']) ;
if (isset($options['debug'])) $options['d'] = $options['debug'];
if (!isset($options['f'])) Msg::fatal("Argument 1 missing");
$file = trim($options['f']);

// --------- Parsing

$sources = parse\Source::from_file($file) ;
if ($source === false) Msg::fatal("Failed to get a valid Source object.");
$source->tokenize();
$source->treefy();
parse\Expression::parse($source->root);

$parser = new Parser();
$parser->parse([$sources]);

// --------- Output

$source->trim_tokens();
if (isset($options['d'])) $source->dump();