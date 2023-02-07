<?php

namespace Tivins\Abstract;

class Output_TTY implements IOutput
{
    static public function get_open_code($type)
    {
        switch ($type) {
            case Output::error:      return "\e[1;4;97;41m";
            case Output::text_error: return "\e[33m";
            case Output::warn:       return "\e[1;97;43m";
            case Output::dim:        return "\e[2m";
            case Output::high:       return "\e[1m";
            case Output::success:    return "\e[1;97;42m";
        }
        return '';
    }

    public function write($type, string $message):string
    {
        return self::get_open_code($type) . $message . "\e[0m";
    }
}