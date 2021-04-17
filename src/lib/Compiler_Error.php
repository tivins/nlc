<?php

use parse\Token;

class Compiler_Error {
    const unexpected_token   = "unexpected token" ;
    const unexpected_keyword = "unexpected keyword" ;
    const unexpected_oef     = "unexpected end of file" ;
    const structure_error    = "structure error" ;
    const info               = "information" ;
    const bad_package        = "bad package" ;
    const syntax             = "syntax error" ;

    public static function emit(string $err, Token $token) {
        Msg::error($err . '//' . $token, $token->file, $token->line, $token->coln);
    }
}