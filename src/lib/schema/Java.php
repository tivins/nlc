<?php

/**
 * Provides the description of the Java Language.
 */
class Schema_Java
{
    static public function property_schema():array
    {
        // $schema = "
        //     +(1?:keyword=static)
        //     +(1?:keyword=private,public,internal)
        //     +(1:word)
        //     +(1?:schema=
        //         +(1:punct=5B)
        //         +(1:number=*/integer|word)
        //         +(1:punct=5D)
        //         )
        //     +(1:word)
        //     +(1?:schema=(
        //         +(1?:punct=3D)
        //         +(1?:*)
        //     )
        //     +(1:punct=3B)
        //     " ;

        $schema_array = [
            ['qty' => 1, 'optional' => 0, 'type' => Token::TYPE_PUNCT,  'data' => ['[']],
            ['qty' => 1, 'optional' => 1, 'type' => Token::TYPE_NUMBER, 'subtype' => Token::SUBTYPE_INTEGER],
            ['qty' => 1, 'optional' => 0, 'type' => Token::TYPE_PUNCT,  'data' => [']']],
        ] ;
        $schema_defval = [
            ['qty' => 1, 'optional' => 0, 'type' => Token::TYPE_PUNCT,  'data' => ['=']],
            ['qty' => 1, 'optional' => 0, 'type' => Token::TYPE_ANY],
            ['qty' => 1, 'optional' => 0, 'type' => Token::TYPE_PUNCT,  'data' => [';']],
        ] ;
        $schema = [
            ['qty' => 1, 'optional' => 1, 'type' => Token::TYPE_KEYWORD, 'data' => ['static']],
            ['qty' => 1, 'optional' => 1, 'type' => Token::TYPE_KEYWORD, 'data' => ['private','public','internal']],
            ['qty' => 1, 'optional' => 0, 'type' => Token::TYPE_WORD],
            ['qty' => 1, 'optional' => 1, 'type' => 'schema', 'data' => $schema_array],
            ['qty' => 1, 'optional' => 0, 'type' => Token::TYPE_WORD],
            ['qty' => 1, 'optional' => 1, 'type' => 'schema', 'data' => $schema_defval],
            ['qty' => 1, 'optional' => 0, 'type' => Token::TYPE_PUNCT, 'data' => [';']],
        ] ;
        return $schema ;
    }

    static public function method_schema():array
    {
        // public String get_name() const { }

        $schema = [
            ['qty' => 1, 'optional' => 1, 'type' => Token::TYPE_KEYWORD, 'data' => ['static','override','virtual']],
            ['qty' => 1, 'optional' => 1, 'type' => Token::TYPE_KEYWORD, 'data' => ['private','public','internal']],
            ['qty' => 1, 'optional' => 0, 'type' => Token::TYPE_WORD],
            ['qty' => 1, 'optional' => 0, 'type' => Token::TYPE_WORD],
            ['qty' => 1, 'optional' => 0, 'type' => Token::TYPE_PUNCT, 'data' => ['(']],
            ['qty' => 1, 'optional' => 0, 'type' => Token::TYPE_PUNCT, 'data' => [')']],
            ['qty' => 1, 'optional' => 1, 'type' => Token::TYPE_KEYWORD, 'data' => ['const']],
            ['qty' => 1, 'optional' => 0, 'type' => Token::TYPE_PUNCT, 'data' => ['{']],
            ['qty' => 1, 'optional' => 0, 'type' => Token::TYPE_PUNCT, 'data' => ['}']],
        ] ;
        return $schema ;
    }

    static public function enum_schema():array
    {
        // public enum NAME { }

        $schema = [
            ['qty' => 1, 'optional' => 1, 'type' => Token::TYPE_KEYWORD, 'data' => ['private','public']],
            ['qty' => 1, 'optional' => 0, 'type' => Token::TYPE_KEYWORD, 'data' => ['enum']],
            ['qty' => 1, 'optional' => 0, 'type' => Token::TYPE_WORD],
            ['qty' => 1, 'optional' => 0, 'type' => Token::TYPE_PUNCT, 'data' => ['{']],
            ['qty' => 1, 'optional' => 0, 'type' => Token::TYPE_PUNCT, 'data' => ['}']],
        ] ;
        return $schema ;
    }

    static public function class_schema():array
    {
        $schema = [
            ['qty' => 1, 'optional' => 1, 'type' => Token::TYPE_KEYWORD, 'data' => ['public','internal']],
            ['qty' => 1, 'optional' => 0, 'type' => Token::TYPE_KEYWORD, 'data' => ['class','interface']],
            ['qty' => 1, 'optional' => 0, 'type' => Token::TYPE_WORD],
            ['qty' => 1, 'optional' => 0, 'type' => Token::TYPE_PUNCT, 'data' => ['{']],
            ['qty' => 1, 'optional' => 0, 'type' => Token::TYPE_PUNCT, 'data' => ['}']],
        ] ;
        return $schema ;
    }

    static public function package_schema():array
    {
        $schema = [
            ['qty' => 1, 'optional' => 0, 'type' => Token::TYPE_KEYWORD, 'data' => ['package']],
            ['qty' => 1, 'optional' => 0, 'type' => Token::TYPE_WORD],
            ['qty' => 1, 'optional' => 0, 'type' => Token::TYPE_PUNCT, 'data' => ['{']],
            ['qty' => 1, 'optional' => 0, 'type' => Token::TYPE_PUNCT, 'data' => ['}']],
        ] ;
        return $schema ;
    }
}