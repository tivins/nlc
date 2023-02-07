<?php

namespace Tivins\ObjectModel;
use Tivins\parse\Token;

class OClass extends OObject
{
    private $class_type = 'class' ;
    private $access = 'internal' ;
    public $extends = [] ;
    public $methods = [] ;
    public $enums = [] ;
    public $properties = [] ;
    public $package = null ;

    public function __construct(Token $root_token, OPackage $package)
    {
        parent::__construct() ;
        $this->package = $package ;
        $this->root_token = $root_token ;
    }
    public function set_access($access) {
        if ($access != 'public' && $access != 'internal') {
            die('[todo] error not public or internal'."\n") ;
        }
        $this->access = $access ;
    }
    public function set_class_type($type) {
        if ($type != 'class' && $type != 'interface') {
            die('[todo] error not class or interface'."\n") ;
        }
        $this->class_type = $type ;
    }
    public function add_method(OMethod $method) { $this->methods[$method->name] = $method ; }
    public function add_property(OProperty $property) { $this->properties[$property->name] = $property ; }
    public function add_enum(OEnum $enum) { $this->enums[$enum->name] = $enum ; }

    public function get_class_type() { return $this->class_type ; }
    public function get_access() { return $this->access ; }
    public function get_methods() { return $this->methods ; }
    public function get_properties() { return $this->properties ; }
    public function get_enums() { return $this->enums ; }

    public function get_fqn() {
        $fqn = $this->package->get_fqn() ;
        return $fqn . '.' . $this->name ;
    }
    public function get_fq_chain() {
        $chain = $this->package->get_fq_chain() ;
        $chain[] = $this ;
        return $chain ;
    }
}