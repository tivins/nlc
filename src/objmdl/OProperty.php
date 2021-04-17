<?php

namespace objmdl;
use \parse\Token;

class OProperty extends OObject {
    public $type = null ;
    public $static = false ;
    public $abstract = false ;
    private $access = 'private' ;
    public $default_value = false ;
    public $schema = [] ;
    public $class = null ;

    public function __construct(Token $root_token, OClass $class)
    {
        parent::__construct() ;
        $this->class = $class ;
        $this->root_token = $root_token ;
    }

    public function get_fqn() {
        $fqn = $this->class->get_fqn() ;
        return $fqn . '.' . $this->name ;
    }
    public function get_fq_chain() {
        $chain = $this->class->get_fq_chain() ;
        $chain[] = $this ;
        return $chain ;
    }
    public function get_access() { return $this->access ; }
    public function set_access($access) {
        if ($access != 'public' && $access != 'private' && $access != 'internal') {
            die('[todo] error not public, private or internal'."\n") ;
        }
        $this->access = $access ;
    }
}