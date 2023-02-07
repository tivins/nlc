<?php
namespace Tivins\ObjectModel;

// class Unary_Op extends Instruction {
//     public function __construct() {
//     }
// }

class For_Loop extends Instruction {
    public function __construct() {
        parent::__construct() ;
        $this->name = 'for' ;
    }
}

class While_Loop extends Instruction {
    public function __construct() {
        parent::__construct() ;
        $this->name = 'while' ;
    }
}

class If_Cond extends Instruction {
    public function __construct() {
        parent::__construct() ;
        $this->name = 'if' ;
    }
}

class Call_Func extends Instruction {
    public function __construct() {
        parent::__construct() ;
        $this->name = 'call' ;
    }
}

// class Expression
// class Variable
    // static get_true()
    // static get_false()
    // static get_null()

class Value {
}

