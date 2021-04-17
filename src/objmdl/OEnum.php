<?php

namespace objmdl;
use parse\Token;

class OEnum extends Instruction {
    private $access = 'private' ;
    public $class = null ;
    private $collection = [] ;
    private $parsed = false ;

    public function __construct(Token $root_token, OClass $class)
    {
        parent::__construct() ;
        $this->class = $class ;
        $this->root_token = $root_token ;
    }
    public function set_parsed() { $this->parsed = true ; }
    public function get_is_parsed() { return $this->parsed ; }

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
    public function get_collection() { return $this->collection ; }
    public function add_item($word, $value = null) {

        /* todo : check if already exists */

        if (is_null($value)) {
            if (empty($this->collection)) {
                $value = 0 ;
            } else {
                $last = end($this->collection) ;
                $value = $last->get_value() + 1 ;
            }
        }

        $this->collection[$word] = new OEntry();
        $this->collection[$word]->set_name($word) ;
        $this->collection[$word]->set_value((int)$value) ;
        $this->collection[$word]->set_enum($this) ;

        return $this->collection[$word] ;
    }
}