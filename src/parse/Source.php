<?php

namespace parse;

use Msg;

/**
 * Defines a ~~code~~ text source element.
 *
 * @author  Tivins
 * @year    2017-2018
 * @see     class `Walker`.
 */
class Source
{
    /**
     * Identifie la source de manière unique.
     * Généralement, il s'agit du nom du fichier source.
     */
    public $name = '' ;

    /**
     * Il s'agit du jeton racine pour cette source.
     * Il est créé dans le constructeur et les jetons
     * créés lors de l'analyse syntaxique seront
     * ajoutés en tant que frère de ce jeton.
     */
    public $root = null ;

    /**
     * Utilisé par le `tokenizer`, permet de se déplacer
     * dans la chaine du code source, en gardant la
     * ligne, la colonne et l'index, à travers les
     * différentes fonctions de parsing.
     */
    private $walker = null ;

    /**
     * Cette variable permet de limiter l'écriture du code.
     * Elle est utilisé au moment du tokenizer pour établir
     * un ordre next/previous, facilement.
     */
    private $last_added_token = null ;

    /**
     * Nous avons besoin du parseur pour acquérir le scope "vraiment"
     * global du projet. C'est bien le parseur qui centralise les
     * informations et permet de garder des références après la
     * suppression des sources.
     */
    private $parser = null ;

    /**
     * Creates a new Source object and initialize its dependencies.
     *
     * @param $source_name
     *      The name of the given source, used to identify this source during
     *      messages.
     *
     * @param $source
     *      The full source code to check and parse.
     *
     * @see class `parse\Walker`
     * @see `Source::from_file`
     */
    public function __construct(string $source_name, string $source)
    {
        $this->name = $source_name ;
        $this->root = new Token(['type' => Token::TYPE_ROOT, 'file' => $this->name]) ;
        $this->last_added_token = $this->root ;
        $this->walker = new Walker($source) ;
    }

    /**
     * Creates a new Source object from the physical given filename.
     *
     * This function is a shortcut which use `file_get_contents()` to load and
     * check the file, creates the `Source` object using the file name and
     * content.
     *
     * @return  Returns a valid `Source` object or `false` if the file doesn't
     *          exists or if it's not readable.
     *
     * @todo    To transform `false` to `null` and use nullable
     *          return type: `:?Source`.
     */
    static public function from_file(string $filename)
    {
        if (!file_exists($filename) || !is_readable($filename)) {
            Msg::error("File not readable '$filename'.");
            return false;
        }

        $file = file_get_contents($filename) ;
        if ($file === false) {
            Msg::error("File not readable '$filename'.");
            return false;
        }

        return new Source(basename($filename), $file) ;
    }

    /**
     * Check if the source is empty (eg: no tokens).
     *
     * @return  True if the tree contains only the "root" token.
     * @todo    Create test case for this function.
     */
    public function is_empty():bool
    {
        return is_null($this->root->next);
    }

    /**
     * @return void
     */
    public function dump()
    {
        Msg::info("Source Dump: '{$this->name}' (original length: " . $this->walker->get_length().' bytes)') ;
        Token::display_tree($this->root) ;
    }

    /**
     * @deprecated Do not use it.
    public function parse(Parser $parser)
    {
        $this->parser = $parser ;

        $this->tokenize() ;
        $this->recognize() ;
        if (!$this->treefy()) Msg::error("Treefy error on {$this->name}") ;
        Token::display_tree($this->root) ;
        $this->semantic() ;
        // $this->rewrite() ;
        Token::display_tree($this->root) ;
    }
     */

    /**
     * @deprecated Do not use it.
     */
    private function semantic()
    {
        $semantic = new Semantic() ;
        $semantic->parser = $this->parser ;
        $semantic->root = $this->root ;
        $semantic->run() ;
    }

    /**
     * @deprecated Do not use it.
     */
    public function recognize()
    /*^
        Première passe de reconnaissance des jetons
        de type "mot" (word).
    */
    {
        $level = 0 ;
        $current = $this->root ;
        while ($current)
        {
            if ($current->type == Token::TYPE_WORD)
            {
                if (in_array($current->data, [
                    'class','public','private','static','internal','virtual',
                    'override','abstract','interface','extends','implements',
                    'return','new','delete','const','package','use','while',
                    'for','if','else','break','switch','case','this',
                    'parent','self','enum','typedef','import',
                    ]))
                {
                    $current->type = Token::TYPE_KEYWORD ;
                }
                // if (in_array($current->data, ['String','int','bool','char','void']))
                // {
                //     $current->type = Token::TYPE_TYPENAME ;
                // }
            }
            Token::browse($current, $level) ;
        }
    }

    private function is_opening(Token $tk) {
        return $tk->type == Token::TYPE_PUNCT &&
            in_array($tk->data, ['(','[','{']);
    }

    private function is_closing(Token $tk) {
        return $tk->type == Token::TYPE_PUNCT &&
            in_array($tk->data, [')',']','}']);
    }

    /**
     * @todo check if useful, see Token functions.
     */
    private function get_next_non_white(Token $tk) {
        while ($tk) {
            $tk = $tk->next ;
            if ($tk->type != Token::TYPE_WHITE) {
                break ;
            }
        }
        return $tk ;
    }

    static public function closing_match_opening(Token $actual_parent, Token $token) {
        $required = -1 ;
        if ($actual_parent->data == '(') $required = ')';
        if ($actual_parent->data == '{') $required = '}';
        if ($actual_parent->data == '[') $required = ']';
        return ($token->data == $required) ;
    }

    /**
     * @return  Returns `true` if the operation was successfully done
     *          or `false` is an error was occured.
     */
    public function treefy():bool
    {
        // Msg::debug(__function__);
        $level = 0 ; /* profondeur */
        $required = 0 ; /* Précise le token attendu pour la fermeture */
        $tk = $this->root ;
        $tkparent = null ;
        while ($tk)
        {
            /// Si on trouve un jeton d'ouverture de bloc '(', '{', '['.
            if ($tk && $this->is_opening($tk))
            {
                $level++;
                $next = $tk->next ;
                if (!$next) return false ;

                /// CAS PARTICULIER des elements "autofermés" :
                if (($tk->data == '{' && $next->data == '}') ||
                    ($tk->data == '(' && $next->data == ')') ||
                    ($tk->data == '[' && $next->data == ']')
                   )
                {
                    $tk->parent = $tkparent;

                    /// On passe au jeton suivant si il existe.
                    $tk = $next ;
                    $tk->parent = $tkparent ;
                    $tk = $tk->next ;
                    if ($tk) { // $tk peut ne pas exister.
                        $tk->parent = $tkparent ;
                    }
                    $level--;
                    continue;
                }
                // On définit le parent de ce jeton avec le parent global en cours.
                // tkparent est nul pour les éléments de 1er niveau.
                $tk->parent = $tkparent ;
                // On redéfinit le parent global en cours avec le jeton actuel,
                // pour que les élements suivant deviennent ses enfants (voir else).
                $tkparent = $tk ;
                // On commence l'imbrication (le jeton suivant est le premier
                // enfant de ce jeton).
                $tk->first_child = $tk->next ;
                // Comme c'est le premier enfant, il n'a pas de 'prev', mais on
                // conserve le next, car la suite des jetons sera enfant du jeton
                // en cours, jusqu'à ce que l'on trouve le jeton 'fermant'.
                $tk->first_child->previous = NULL ;
            }

            /// Sinon, si on trouve un jeton de fermeture de bloc ')', '}' ou "fin de chaine de pointage".
            else if ($this->is_closing($tk))
            {
                if (!$tkparent) {
                    if (!$tk->next) {
                        Compiler::emit_error($this, $tk, Compiler_Error::structure_error) ;
                        // printf("\n\n\terr  \n\n[{$tk->file}]\n\n[{$tk->line}]\n\n[{$tk->coln}]\n\n\n");  //?
                        return 1 ;                // [todo] ?
                    }


                    // **TODO**
                    // vThrowCompileError(V_HIERACHY_ERROR_CLOSING, tk) ;
                    // vStringAppendf(tokens.lastError, "hierarchy error on closing '%s' [line=%d,col=%d] next(%d)",
                    //     vTokenGetTypeName(tk), tk->line, tk->col, tknextid(tk));
                    return 0;
                }

                if (!self::closing_match_opening($tkparent, $tk)) {
                    // **TODO**
                    //prog_log(Verbosity_Error, "[line:%d,column:%d] hierarchy error on closing. Token '%d' found, instead of '%d' token.\n",
                    //    tkparent->line + 1, tkparent->column, tk->subtype, required) ;
                    return 0;
                }

                // Maintenant, tkparent est le jeton qui a ouvert le groupe (ex: '{').

                // on fait de ce token le frere (next) du token ouvrant.
                $tkparent->next = $tk;

                if (!$tk->previous) { /*[todo]*/printf("NO PREV\n"); return 0; }
                if (!$tk->previous->parent) { /*[todo]*/printf("NO PREV PARENT %d(Required 0x%02x)\n", $tk->subtype, required); return 0;}

                // tk->previous est encore sont frere, mais va devenir son oncle,
                // et ce dernier n'aura donc plus de frere (next).
                $tk->previous->next = NULL ;
                // On affecte son nouveau frère (prev) avec le jeton ouvrant.
                $tk->previous = $tkparent ;
                // On retourne sur le parent initial (remonte d'un niveau),
                $tkparent = $tkparent->parent;
                // et on l'applique à notre token de fin.
                $tk->parent = $tkparent;

                $level--;
            }


            /// Si c'est autre chose, on affecte le parent global en cours.
            /// on ne change pas la hiérarchie linéaire (prev, next).
            else
            {
                $tk->parent = $tkparent;
            }
            $tk = $tk->next ;
        }
        if ($level > 0)
        {
            \Compiler_Error::emit(\Compiler_Error::structure_error, $tkparent);
        }
        return $level == 0 ;
    }

    /**
     * @return  Actually, returns always `true` or die.
     */
    public function tokenize():bool {

        $wk = $this->walker ; // Shortcut reference

        while ($wk->is_inside())
        {
            $char = $wk->get_char() ;
            // Msg::debug("char[$wk->idx]\t'".\Util::inline_str($char)."'\t".ord($char)) ;

            // -------------------------------------------------------------------
            // strings

            if ($char == "\"" || $char == "'") {
                $token = $this->create_and_push_token(Token::TYPE_STRING, "") ;
                $token->data = $this->parse_string($char) ;
                continue ;
            }

            // -------------------------------------------------------------------
            // inline comments

            if ($char == "/" && $wk->get_relchar(1) == '/') {
                $token = $this->create_and_push_token(Token::TYPE_COMMENT, "") ;
                $token->data = $this->parse_until("\n") ;
                continue ;
            }
            if ($char == "/" && $wk->get_relchar(1) == '*') {
                $token = $this->create_and_push_token(Token::TYPE_COMMENT, "") ;
                $found = false ;
                $token->data = $this->parse_until("*/", $found) ;
                if (!$found) Msg::warn("unclosed comment started.", $token->file, $token->line, $token->coln);
                continue ;
            }

            // -------------------------------------------------------------------
            // whitespace characters

            if (\Punct::is_white($char)) {
                $token = $this->create_and_push_token(Token::TYPE_WHITE, "") ;
                $token->data = $this->parse_group('is_white') ;
                continue ;
            }

            // -------------------------------------------------------------------
            // identifiers, names: (numbers+letters) characters

            if (\Punct::is_letter($char)) {
                $token = $this->create_and_push_token(Token::TYPE_WORD, "") ;
                $token->data = $this->parse_group('is_letter_or_integer_or_dot') ;
                if (strpos($token->data, '..')) {
                    Compiler::emit_warning($this, $token, Compiler_Error::syntax, '`..` detected.') ;
                }
                if (trim($token->data, '.') != $token->data) {
                    Compiler::emit_warning($this, $token, Compiler_Error::syntax, '`.` at the begin or the end of name.') ;
                }
                continue ;
            }

            // -------------------------------------------------------------------
            // numbers

            if (\Punct::is_int($char)) {
                $token = $this->create_and_push_token(Token::TYPE_NUMBER, "") ;
                $token->data = $this->parse_group('is_number') ;
                $token->subtype = mb_strpos($token->data, '.') === false ? Token::SUBTYPE_INTEGER : Token::SUBTYPE_DECIMAL ;
                continue ;
            }

            // -------------------------------------------------------------------
            // punctuation

            if (\Punct::is_punct($char))
            {
                $next_char = $wk->get_relchar(1) ;
                $nextnext_char = $wk->get_relchar(2) ;

                $combinations3 = [
                    '<=>', // Spaceship operator `<=>`
                    '===', // Strict equality `===`
                ];
                $combinations2 = [
                    '++','--',
                    '&&','||',
                    '+=','-=','*=','/=','%=',
                    '&=','^=','|=','~=','==',
                    '<=','>=',
                    '<<','>>',
                ];
                foreach ($combinations3 as $comb) {
                    if ($char == $comb[0] && $next_char == $comb[1] && $nextnext_char == $comb[2]) {
                        $char = $comb;
                        $wk->advance();
                        $wk->advance();
                    }
                }
                foreach ($combinations2 as $comb) {
                    if ($char == $comb[0] && $next_char == $comb[1]) {
                        $char = $comb;
                        $wk->advance();
                    }
                }

                $token = $this->create_and_push_token(Token::TYPE_PUNCT, $char) ;

                // correct column/line token
                $token->coln -= mb_strlen($char) - 1 ;

                $wk->advance() ;
                continue ;
            }

            Msg::error("not in case '$char' (See ".__class__.'::'.__function__.'(), line '.__line__.')') ;
            return false ;
        }
        return true ;
    }

    private function parse_until($end_seq, &$found = false) {
        $wk = $this->walker ;

        $end_len = mb_strlen($end_seq) ;
        $data = '' ;
        while ($wk->is_inside()) {
            $char = $wk->get_char() ;
            $data .= $char ;
            if (mb_substr($data,-$end_len) == $end_seq) {
                $found = true;
                $wk->advance() ;
                break ;
            }
            $wk->advance() ;
        }
        return $data ;
    }

    private function parse_group($func) {
        $wk = $this->walker ;

        $data = '' ;
        while ($wk->is_inside()) {
            $char = $wk->get_char() ;
            if (!\Punct::$func($char)) {
                break ;
            }
            $data .= $char ;
            $wk->advance() ;
        }
        return $data ;
    }

    private function parse_string($start = '"') {
        $wk = $this->walker ;

        $char = $wk->get_char() ;
        $data = $char ; // On ajoute le 1er caractère car il s'agit du caractère ouvrant.
        $wk->advance() ;
        while ($wk->is_inside())
        {
            $last_char = $char ;
            $char = $wk->get_char() ;
            if ($char == $start) {
                $break = true ;
                if ($last_char == '\\' && $wk->get_relchar(-2) != '\\') { // TODO :::: be more ... !
                    $break = false ;
                }
                if ($break) {
                    $data .= $char ; // on ajoute le caractère fermant.
                    $wk->advance() ;
                    break ;
                }
            }
            $data .= $char ;
            $wk->advance() ;
        }
        return $data ;
    }

    /**
     * @param $type A Token::TYPE_* constant.
     */
    private function create_and_push_token(int $type, $data)
    {
        $token = new Token([
            'type' => $type,
            'file' => $this->name,
            'line' => $this->walker->get_line(),
            'coln' => $this->walker->get_column(),
            'data' => $data,
            ]) ;
        $this->last_added_token->set_next($token) ;
        $this->last_added_token = $token ;
        return $token ;
    }

    /**
     * @todo Rename to `remove_whitespace_tokens`
     * @todo Move to Token?
     * @todo Set static.
     */
    public function trim_tokens()
    {
        $level = 0 ;
        $current = $this->root ;
        while ($current) {
            if ($current->check(Token::TYPE_WHITE)) {
                if ($current->previous){
                    if ($current->next)
                       $current->next->previous = $current->previous;
                    $current->previous->next = $current->next;
                }else{
                    // <White> <item>
                }
            }
            Token::browse($current, $level) ;
        }
    }
}