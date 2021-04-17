<?php
namespace objmdl;

class Scope_Stack {
    private $stack = [] ;
    private $global = null ;
    public function __construct(Scope $global) {
        $this->push($global) ;
    }
    public function get_global() { return $this->global ; }
    public function get_depth() { return count($this->stack) ; }
    public function push(Scope $scope) {
        array_push($this->stack, $scope) ;
    }
    public function push_new_scope(OObject $object) {
        $new_scope = new Scope($object) ;
        $this->push($new_scope) ;
    }
    public function get_current() {
        return end($this->stack) ;
    }
    public function get_current_type() {
        return get_class(end($this->stack)->get_ref()) ;
    }
    public function pop(OObject $obj) {
        if ($this->is_global()) {
            Msg::error("Scope stack underflow (you are trying to pop the global stack...).") ;
            return ;
        }

        $cur_scope = end($this->stack) ;
        if ($cur_scope->get_ref() == $obj) {
            // echo "Popped!\n" ;
            /* return */ array_pop($this->stack) ;
        }
    }

    /**
     * Checks if the stack contains only the global scope.
     */
    public function is_global():bool {
        return (count($this->stack) == 1) ;
    }
}