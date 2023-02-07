<?php
namespace Tivins\ObjectModel;

class OEntry extends OObject
{

    private $value = 0 ;

    /**
     * The reference to the own OEnum object.
     */
    private $enum = null ;

    public function set_enum($enum) { $this->enum = $enum ; }
    public function get_value() { return $this->value ; }
    public function set_value($value) { $this->value = $value ; }
    public function get_fqn() {
        $fqn = $this->enum->get_fqn() ;
        return $fqn . '.' . $this->name ;
    }
    public function get_fq_chain() {
        $chain = $this->enum->get_fq_chain() ;
        $chain[] = $this ;
        return $chain ;
    }
}
