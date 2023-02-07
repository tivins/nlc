<?php

namespace Tivins\ObjectModel;
use Msg;

/**
 * Wrapper to acces Tivins\ObjectModel\Lang from a variable.
 */
class Lang
{
    /**
     * Return a schema for specified language if exists.
     * Or an empty array otherwise.
     */
    static public function get_schema(string $name, string $type): array
    {
        $name = mb_strtolower(trim($name));
        $call = ['Schema_' . $name, $type . '_schema'];
        if ($name != 'Common' && is_callable($call)) return $call();
        $call[0] = 'Tivins\Schema\Schema_Common';
        if (is_callable($call)) return $call();
        Msg::warn("schema not found $name, $type.");
        return [];
    }
}