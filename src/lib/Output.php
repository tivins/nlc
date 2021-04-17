<?php

class Output
{
    const error      = 'error';
    const text_error = 'text_error';
    const warn       = 'warn';
    const dim        = 'dim';
    const high       = 'high';
    const success    = 'success';

    static private $output=null;
    static public function set(IOutput $output) { self::$output = $output; }
    static public function write($type, $message) { return self::$output->write($type, $message); }
    static public function write_error($message) { return self::$output->write(self::error, $message); }
    static public function write_text_error($message) { return self::$output->write(self::text_error, $message); }
}

interface IOutput
{
    public function write($type, string $message):string;
}

class Output_HTML implements IOutput
{
    public function write($type, string $message):string
    {
        return '<span class="'.\Util::html($type).'">' . \Util::html($message) . '</span>' ;
    }
}
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