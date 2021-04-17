<?php
/**
 * Wrapper to acces Lang from a variable.
 */
class Lang
{
    /**
     * Return a schema for specified language if exists.
     * Or an empty array otherwise.
     */
    static public function get_schema(String $name, String $type) : Array
    {
        $name = mb_strtolower(trim($name));
        $call = ['Schema_' . $name, $type. '_schema'];
        if ($name != 'Common' && is_callable($call)) return $call();
        $call[0] = 'Schema_Common';
        if (is_callable($call)) return $call();
        Msg::warn("schema not found $name, $type.");
        return [];
    }
}