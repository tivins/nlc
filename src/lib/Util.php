<?php

class Util
{
    static public function inline_str($str)
    {
        $out = '' ;
        $idx = 0 ;
        while ($idx < mb_strlen($str)) {
            $chr = mb_substr($str, $idx, 1) ;
            if ($chr == " ") $chr = '·' ;
            if ($chr == "\n") $chr = '\n' ;
            if ($chr == "\r") $chr = '\r' ;
            if ($chr == "\t") $chr = '\t' ;
            $out .= $chr ;
            $idx++ ;
        }
        return $out ;
    }

    static public function yesno($bool)
    {
        return $bool?"yes":"no";
    }

    static public function get_last_key(&$array)
    {
        end($array);
        return key($array);
    }

    static public function html($str) {
        return htmlentities($str, ENT_QUOTES, 'utf-8');
    }

    static public function chrono_start() { return microtime(true); }
    static public function chrono_duration($start) { return microtime(true) - $start; }
}