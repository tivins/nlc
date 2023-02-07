<?php

namespace Tivins\Schema;
use Token;

class CodeVal
    /*^
        Cette classe permet d'obtenir les validateurs pour les portions de code
        interne aux fonctions (et méthodes).
    */
{
    public static function local_var()
        /*^
            Retourne la structure de validation pour une variable locale.

            Model : (<static><w>)<type><w><name>(<w>)(<=>(<w>)<expression>(<w>))<;>
        */
    {
        $schema = [
            'name'  => 'local_var',
            'scope' => ['function', 'method'],
            'data'  => [
                ['qty' => 1, 'optional' => 1, 'type' => Token::TYPE_KEYWORD, 'data' => ['static']],
                ['qty' => 1, 'optional' => 0, 'type' => Token::TYPE_WORD],
                ['qty' => 1, 'optional' => 0, 'type' => Token::TYPE_WORD],
                ['qty' => 1, 'optional' => 0, 'type' => Token::TYPE_PUNCT, 'data' => [';']],
            ]
        ];
        return $schema;
    }

    public static function unary_op()
    {

        $schema = [
            ['qty' => 1, 'optional' => 0, 'type' => Token::TYPE_WORD],
            ['qty' => 1, 'optional' => 0, 'type' => Token::TYPE_PUNCT, 'data' => ['++', '--']],
        ];
        return $schema;
    }

    public static function cond_block($key = 'for')
        /*^
            Retourne la structure de validation pour une boucle "for", "if", "while".
        */
    {
        $schema = [
            ['qty' => 1, 'optional' => 0, 'type' => Token::TYPE_KEYWORD, 'data' => [$key]],
            ['qty' => 1, 'optional' => 0, 'type' => Token::TYPE_PUNCT, 'data' => ['('], 'block_name' => 'cond'],
            ['qty' => 1, 'optional' => 0, 'type' => Token::TYPE_PUNCT, 'data' => [')']],
            ['qty' => 1, 'optional' => 0, 'type' => Token::TYPE_PUNCT, 'data' => ['{'], 'block_name' => 'body'],
            ['qty' => 1, 'optional' => 0, 'type' => Token::TYPE_PUNCT, 'data' => ['}']],
        ];
        return $schema;
    }

    public static function call_func()
        /*^
            Retourne la structure de validation pour une boucle "for", "if", "while".
        */
    {
        $schema = [
            ['qty' => 1, 'optional' => 0, 'type' => Token::TYPE_WORD],
            ['qty' => 1, 'optional' => 0, 'type' => Token::TYPE_PUNCT, 'data' => ['('], 'block_name' => 'cond'],
            ['qty' => 1, 'optional' => 0, 'type' => Token::TYPE_PUNCT, 'data' => [')']],
        ];
        return $schema;
    }
}