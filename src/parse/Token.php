<?php

namespace parse;


class Token extends \Node {

    const TYPE_UNKNOWN =        0x00 ;
    const TYPE_ROOT =           0x01 ;
    const TYPE_ANY =            0x02 ;
    const TYPE_WORD =           0x03 ;
    const TYPE_WHITE =          0x04 ;
    const TYPE_PUNCT =          0x05 ;
    const TYPE_STRING =         0x06 ;
    const TYPE_NUMBER =         0x07 ;
    const TYPE_COMMENT =        0x08 ;
    const TYPE_KEYWORD =        0x09 ;
    const TYPE_TYPENAME =       0x0A ;
    const TYPE_BLOCK =          0x0B ;
    const TYPE_SYMBOL_NAME =    0x0C ;

    const SUBTYPE_NONE =        0x00 ;
    const SUBTYPE_UNKNOWN =     0x01 ;
    const SUBTYPE_START =       0x02 ;
    const SUBTYPE_END =         0x03 ;
    const SUBTYPE_INTEGER =     0x04 ;
    const SUBTYPE_DECIMAL =     0x05 ;

    static public function type_name(int $type):string {
        switch ($type) {
            case self::TYPE_UNKNOWN: return 'unknown' ;
            case self::TYPE_ROOT: return 'root' ;
            case self::TYPE_ANY: return 'any' ;
            case self::TYPE_WORD: return 'word' ;
            case self::TYPE_WHITE: return 'whitespace' ;
            case self::TYPE_PUNCT: return 'punctuation' ;
            case self::TYPE_STRING: return 'string' ;
            case self::TYPE_NUMBER: return 'number' ;
            case self::TYPE_COMMENT: return 'comment' ;
            case self::TYPE_KEYWORD: return 'keyword' ;
            case self::TYPE_TYPENAME: return 'typename' ;
            case self::TYPE_BLOCK: return 'block' ;
            case self::TYPE_SYMBOL_NAME: return 'symbol name' ;
        }
    }
    static public function subtype_name(int $type):string {
        switch ($type) {
            case self::SUBTYPE_NONE: return 'none' ;
            case self::SUBTYPE_START: return 'start' ;
            case self::SUBTYPE_END: return 'end' ;
            case self::SUBTYPE_INTEGER: return 'integer' ;
            case self::SUBTYPE_DECIMAL: return 'decimal' ;
            case self::SUBTYPE_UNKNOWN:
            default: return 'unknown' ;
        }
    }

    /**
     * Gets the type value from the given name.
     *
     * @return The value of a constant `TYPE_*`, `TYPE_UNKNOWN` if not found.
     */
    static public function get_type_from_name(string $type_name):int {
        switch ($type_name) {
            case 'TYPE_NUMBER': return self::TYPE_NUMBER;
            case 'TYPE_ROOT': return self::TYPE_ROOT;
            case 'TYPE_WORD': return self::TYPE_WORD;
            case 'TYPE_PUNCT': return self::TYPE_PUNCT;
            case 'TYPE_STRING': return self::TYPE_STRING;
            default:
                \Msg::warn("Cannot found type from name '$type_name'. See: `".__class__.'::'.__function__."()`.");
                return self::TYPE_UNKNOWN;
        }
    }

    static private $counter = 1 ;
    public $id = 0 ;

    public $file = '' ;
    public $line = 0 ;
    public $coln = 0 ;

    public $type = 0 ;
    public $subtype = 0 ;
    public $data = '' ;
    public $prog_ref = null ;

    /**
     * Creates a new Token instance.
     *
     * @param opts
     *      Associative array with initialization values.
     *
     *      Example:
     *      ```
     *      $root = new Token(['type' => Token::TYPE_ROOT, 'file' => 'foo.java']);
     *      ```
     * @see class `Node`.
     */
    public function __construct(array $opts = []) {
        $this->id = self::$counter++ ;

        foreach (['line','coln','file','type','data'] as $item) {
            if (isset($opts[$item])) $this->$item = $opts[$item] ;
        }
    }

    /**
     * Checks if the current token matches to the given criteria.
     */
    public function check($type = Token::TYPE_ANY, /*?string*/ $data = null) {
        // var_dump([
        //     'checktype'=>$type,
        //     'this.type'=>$this->type,
        //     'is_null($data)'=>is_null($data),
        //     'if($data)'=>$data?$data:'**fail**',
        //     '$data === $this->data'=>$data === $this->data,
        // ]);
        return ($type == Token::TYPE_ANY || $this->type == $type) &&
               (is_null($data) || ($data === $this->data)) ;
    }

    // public function is_white() { return $this->type == self::TYPE_WHITE ; }

    /**
     * @deprecated Use `Token::check()` instead.
     * @see `Token::check()`
     */
    public function is_word():bool { return $this->type == self::TYPE_WORD ; }
    public function is_non_code() { return $this->type == self::TYPE_WHITE || $this->type == self::TYPE_COMMENT ; }

    public function to_string() {
        //{$this->line};{$this->coln}
        return "[Token(".$this->id."):"
                ."data=\"".\Util::inline_str($this->data)."\" (".gettype($this->data)."),type=".self::type_name($this->type).",subtype=".self::subtype_name($this->subtype).','
                ."prog_ref=".($this->prog_ref?get_class($this->prog_ref).'"'.$this->prog_ref->name.'"':'null')
              ."]";
        // . " (".mb_strlen($this->data).":)" ;
    }

    /**
     * @see Calls `to_string()`.
     */
    public function __toString():string {
        return $this->to_string() ;
    }

    static public function concat_tokens(array $list) {
        $str = '' ;
        foreach ($list as $token) {
            $str .= $token->data ;
        }
        return $str ;
    }

    /**
     * Parcours l'arbre en profondeur et appelle les callbacks
     * définies (ou non) via les clés `'up'`, `'down'`, `'next'`.
     *
     * @example Basic usage
     * ```
     * $current = $root;
     * $level = 0;
     * while ($current) {
     *    echo $current, PHP_EOL;
     *    Token::browse($current, $level);
     * }
     * ```
     *
     * @param current
     *      A reference to a token.
     *
     * @return void
     */
    static public function browse(Token &$current, int &$level, array $callbacks = [])
    {
        if ($current->first_child)
        {
            // \Msg::debug("Browse first_child");
            $current_save = $current ;
            $current = $current->first_child ;
            $level++ ;
            if (isset($callbacks['down'])) { call_user_func_array($callbacks['down'], [$current_save, $current]) ; }
            return ;
        }
        if ($current->next)
        {
            // \Msg::debug("Browse first_next -> ".var_export($current->next->id,1));
            $current_save = $current ;
            $current = $current->next ;
            if (isset($callbacks['next'])) { call_user_func_array($callbacks['next'], [$current_save, $current]) ; }
            return ;
        }
        if ($current->parent && $current->parent->next)
        {
            // \Msg::debug("Browse parent next");
            $current_save = $current->parent ;
            $current = $current->parent->next ;
            $level-- ;
            if (isset($callbacks['up'])) { call_user_func_array($callbacks['up'], [$current_save, $current]) ; }
            return ;
        }
        $current = null ;
    }

    static public function skip_non_code(Token &$current, &$skipped = null)
    {
        while ($current->check(Token::TYPE_WHITE) || $current->check(Token::TYPE_COMMENT))
        {
            if ($skipped) $skipped[] = $current ;
            $current = $current->next ;
            if (!$current) return null ;
        }
    }

    /**
     * Find and return the next or the previous non-code token from the current.
     *
     * @return  Returns the (next|previous)-linear non-code token,
     *          or null, if not found.
     * @todo    To create unit test case.
     */
    public function get_next_non_code(String $dir = self::DIR_RIGHT) /* :?Token */
    {
        $current = $this ;
        $current = $current->$dir ;
        while ($current) {
            if (!$current->check(Token::TYPE_WHITE) &&
                !$current->check(Token::TYPE_COMMENT)) {
                break;
            }
            $current = $current->$dir;
        }
        return $current ;
    }

    static public function display_tree(Token $root)
    {
        $cntfmt = "% ".strlen((string)self::$counter)."d" ;

        $level = 0 ;
        $current = $root ;
        while ($current)
        {
            \Msg::debug(sprintf(
                "$cntfmt: [p:$cntfmt,pv:$cntfmt,nx:$cntfmt|% 3d.% 3d|l% 2d] %s %s",
                $current->id,
                $current->parent ? $current->parent->id : 0,
                $current->previous ? $current->previous->id : 0,
                $current->next ? $current->next->id : 0,
                $current->line,
                $current->coln,
                $level,
                str_repeat('   ', $level),
                $current->to_string()
                )
            ) ;

            Token::browse($current, $level) ;
        }
    }
}
