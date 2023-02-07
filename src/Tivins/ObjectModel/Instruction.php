<?php
namespace Tivins\ObjectModel;

class Instruction extends OObject
{
    public $blocks = [] ;
    public $codes = [] ;

    public function add_code(Instruction $code, $block_name = 'body')
    {
        $this->codes[$block_name][] = $code ;
    }
    public function get_codes() { return $this->codes ; }
}