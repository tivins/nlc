<?php
namespace Tivins\ObjectModel;
use Tivins\parse\Token;

class OMethod extends Instruction
{
    private bool $is_const = false ;
    public bool $static = false ;
    private string $access = 'internal' ;
    private $return = null ;
    public array $local_vars = [] ;
    public $class = null ;

    public function __construct(Token $root_token, OClass $class)
    {
        parent::__construct() ;
        $this->class = $class ;
        $this->root_token = $root_token ;
    }

    public function add_local_var(Variable $var)
    {
        $this->local_vars[] = $var ;
    }
    public function get_fqn():string {
        $fqn = $this->class->get_fqn() ;
        return $fqn . '.' . $this->name ;
    }
    public function get_fq_chain():array {
        $chain = $this->class->get_fq_chain() ;
        $chain[] = $this ;
        return $chain ;
    }
    public function get_is_const():bool { return $this->is_const ; }
    public function set_const(bool $is_const): void {
        $this->is_const = $is_const ;
    }
    public function get_return() { return $this->return; }
    public function set_return($return): void {
        $this->return = $return ;
    }
    public function get_access():string { return $this->access ; }
    public function set_access(string $access): void {
        if ($access != 'public' && $access != 'private' && $access != 'internal') {
            die('[todo] error not public, private or internal'."\n") ;
        }
        $this->access = $access ;
    }
}