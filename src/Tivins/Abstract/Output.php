<?php

namespace Tivins\Abstract;


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
