<?php

namespace Tivins\Schema;

use Tivins\Abstract\Punct;
use Tivins\parse\Token;

class Schema_Common
{
    /**
     * Check for left-to-right schema
     */
    static public function number_operation_schema()
    {
        $schema = [
            ['qty' => 1, 'optional' => 0, 'type' => Token::TYPE_NUMBER],
            ['qty' => 1, 'optional' => 0, 'type' => Token::TYPE_PUNCT, 'data' => Punct::get_operators()],
            ['qty' => 1, 'optional' => 0, 'type' => Token::TYPE_NUMBER],
        ];
        return $schema;
    }

    /**
     * Gets the list of the operators ordered by precedence.
     *
     * @see https://secure.php.net/manual/en/language.operators.precedence.php
     * @see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Operators/Operator_Precedence
     * @see https://doc.rust-lang.org/reference/expressions.html#expression-precedence
     */
    static public function get_operators_precedence()
    {
        // % remainder
        return [
            ['*', '/', '%'],
            ['-', '+'],
            ['<<', '>>'],
            ['<', '<=', '>', '>='],
            ['==', '!=', '===', '!=='],
            ['&'],
            ['^'],
            ['|'],
            ['&&'],
            ['||'],
        ];
    }

    static public function get_operators()
    {
        return array_merge(...self::get_operators_precedence());
    }
}