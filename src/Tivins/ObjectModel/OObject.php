<?php

namespace Tivins\ObjectModel;
use Tivins\parse\Token;

class OObject
{
    static private $counter = 1 ;
    private $id = 0 ;
    private $root_token = null ;

    public function __construct() {
        $this->id = self::$counter++ ;
    }

    public $name = "" ;

    // public $errors = [] ; // todo ?
    public function get_name():string { return $this->name ; }
    public function set_name(string $name) { $this->name = $name ; }
    public function __toString():string { return get_class($this) . ' (' . $this->name . ') #' . $this->id . $this->root_token ; }
    public function set_root_token(Token $root_token) { $this->root_token = $root_token ; }
    public function get_root_token()/*:?Token*/ { return $this->root_token ; }
}
