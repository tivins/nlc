<?php


namespace Tivins\Abstract;
/**
 * @deprecated
 */
class Punct
{

    static $underscode_as_letter = true;

    static public function is_int($char)
    {
        return (ord($char) >= ord('0') && ord($char) <= ord('9'));
    }

    static public function get_unary()
    {
        return ['+', '-'];
    }

    static public function get_operators()
    {
        return ['+', '-', '*', '/', '%', '<<', '>>'];//,'&','|','~'];
    }
    // static public function is_operator($char) {
    //     return in_array($char, self::get_operators());
    // }
    static public function is_number($char)
    {
        return (self::is_int($char) || $char == '.');
    }

    static public function is_letter($char)
    {
        return (ord($char) >= ord('a') && ord($char) <= ord('z'))
            || (ord($char) >= ord('A') && ord($char) <= ord('Z'))
            || (self::$underscode_as_letter && $char == '_');
    }

    static public function is_letter_or_integer($char)
    {
        return self::is_letter($char) || self::is_int($char);
    }

    static public function is_letter_or_integer_or_dot($char)
    {
        return self::is_letter($char) || self::is_int($char) || $char == '.';
    }

    static public function is_white($char)
    {
        return in_array(ord($char), [9, 10, 13, 32]);
    }

    static public function is_punct($char)
    {
        static $available;
        if (!isset($available)) {
            $available = [
                ord(','), ord(";"), ord("("), ord(")"),
                ord('{'), ord("}"), ord("["), ord("]"),
                ord('-'), ord("/"), ord("="), ord("+"),
                ord('_'), ord("!"), ord("."), ord("%"),
                ord('~'), ord("|"), ord("?"), ord(","),
                ord('<'), ord(">"), ord("&"), ord("`"),
                ord('^'), ord('²'),
                ord(':'),
                ord('*'),
            ];
        }
        return in_array(ord($char), $available);
    }

}
