<?php

define('NOVLANG_PARSER_VERSION', [1, 0]);

/**
 * Register the autoload function.
 */
spl_autoload_register(function($class)
{
    // echo __function__ . '//'.$class."\n";
    // debug_print_backtrace();

    $path = explode('\\',$class);
    $class = end($path);

    $file = __dir__ . '/' . implode('/',$path) . '.php';
    if (file_exists($file)) { include $file; return; }

    $dirs = ['lib'/*,'objmdl','review'*/];
    foreach ($dirs as $dir) {
        $file = __dir__ . '/' . $dir . '/' . $class . '.php';
        if (file_exists($file)) { include $file; return; }
    }

    // Second match: folders.
    $parts = explode('_', $class);
    $parts = array_map('strtolower', $parts);
    $parts[count($parts)-1] = ucfirst(end($parts));
    $file = __dir__ . '/lib/' . implode('/',$parts) . '.php';
    if (file_exists($file)) { include $file; return; }
});

/// Catch exceptions to use in the internal messaging system.
set_exception_handler(function ($ex) { Msg::exception($ex); });

/// Catch PHP errors to use in the internal messaging system.
set_error_handler(function(int $errno, string $errstr, string $errfile = '', int $errline = 0, array $errcontext = []) {
    Msg::error("PHP: " . $errstr, $errfile, $errline) ;
});

