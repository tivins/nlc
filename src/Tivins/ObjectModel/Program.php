<?php

namespace Tivins\ObjectModel;
use Tivins\parse\Token;

class Program
{
    private $root_package = null ;
    private $global_scope = null ;
    private $scope_stack = null ;
    private $symbols_map = [] ;
    private $all_instructions = [] ;

    public function __construct(Token $root)
    {
        $this->root_package = new OPackage($root) ;
        $this->root_package->name = "global" ;
        $this->global_scope = new Scope($this->root_package) ;
        $this->scope_stack = new Scope_Stack($this->global_scope) ;
    }

    public function display() { return $this->root_package->display() ; }

    public function get_root_package():OPackage { return $this->root_package ; }

    public function get_scope_stack():Scope_Stack { return $this->scope_stack ; }

    public function get_symbols_names():Array { return array_keys($this->symbols_map) ; }

    public function get_symbol($name)/*:?OObject*/
    {
        if (!isset($this->symbols_map[$name])) return null ;
        return $this->symbols_map[$name] ;
    }

    public function add_symbol(OObject $obj)
    {
        if (isset($this->symbols_map[$obj->get_fqn()])) {
            \Msg::warn($obj->get_fqn(). ' symbol already exists in program.');
        }
        $this->symbols_map[$obj->get_fqn()] = $obj ;
    }

    public function add_instruction(Instruction $obj)
    {
        $this->all_instructions[] = $obj ;
    }

    public function get_instructions():Array
    {
        return $this->all_instructions ;
    }
}