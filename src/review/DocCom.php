<?php

class DocCom
{
    static public function parse($doc)
    {
        $infos = ['blocs' => []];

        $doc = trim($doc);
        $chars = count_chars($doc);

        // supprime le '/**' et '*/'.
        $doc = substr($doc, 3, -2);

        // supprimer le ' * ' initial.
        $doc = preg_replace('~^\s*\* ?~m', '', $doc);

        $doc = trim($doc);

        //$doc = htmlentities($doc, ENT_QUOTES, 'utf-8');

        $doc = preg_replace_callback('~^([^@]+)~', function($matches) use(&$infos) {
            $infos['brief'] = trim($matches[1]);
            return '';
            return '<p>'.nl2br(trim($matches[1])).'</p>'."\n";
        }, $doc);

        $doc = preg_replace_callback('~^@(\w+)\s*([^@]+)~m', function($matches) use (&$infos) {
            $type = $matches[1];
            $infos['blocs'][$type][] = trim($matches[2]);
            return '';
            return '<p class="aro-'.$type.'"><b>'.$type.'</b>: '.nl2br(trim($matches[2])).'</p>';
        }, $doc);

        if (!empty($infos['blocs']['param'])) {
            foreach ($infos['blocs']['param'] as $name => $str) {
                $strexp = explode(' ',$str,2);
                $infos['params'][ltrim(trim($strexp[0]),'$')] = $strexp[1];
            }
            unset($infos['blocs']['param']);
        }

        // $doc = preg_replace('~`(.*?)`~', '<code>$1</code>', $doc);

        // if ($chars[ord('*')] > 5 && $chars[ord('@')] > 2) {
        //     var_dump($infos, $doc);
        //     die;
        // }

        return $infos;
    }

    static public function transform($str)
    {
        global $classnames;

        // Transform HTML entities before to add markup.
        $str = htmlentities($str, ENT_QUOTES, 'utf-8');

        $str = preg_replace_callback('~```((.*\R*)*)```~', function($matches) {
            return '<pre>'.trim($matches[1]).'</pre>';
        }, $str);
        $str = preg_replace('~`(.*?)`~', '<code>$1</code>', $str);
        $str = preg_replace('~\~\~([^\~]+)\~\~~', '<strike class="muted">$1</strike>', $str);

        // Replace links `[label](url)`:
        $str = preg_replace('~\[(.*?)\]\((.*?)\)~', '<a href="$2">$1</a>', $str);

        // escape and replace api-links:
        $cn = array_map('preg_quote',$classnames);
        $str = preg_replace('~\b('.implode('|',$cn).')\b~', '<a href="_$1.html">$1</a>', $str);

        return $str;
    }

    static public function open_ns($fullname)
    {
        $ns_sep='\\';
        return $ns_sep.ltrim(str_replace('\\', $ns_sep, $fullname),'\\');
    }

    static public function show_type(ReflectionType $type = null)
    {
        if (! $type) return '';
        if ($type->isBuiltin()) {
            return '<a class="type builtin" href="https://php.net/' . $type . '">' . $type . '</a>';
        }
        $type_class = Review::get_class($type) ;

        return '<a class="type" href="'.self::get_url_class($type_class).'" title="' . self::open_ns($type_class->getName()) . '">'
            . $type_class->getShortName() . '</a>' ;
    }

    static public function get_url_class(ReflectionClass $class, $with_ext = true) {
        return implode('-',explode('\\',$class->getNamespaceName())).'-'.$class->getShortName()
            .($with_ext?'.html':'');
    }
    static public function get_url_method(ReflectionMethod $method, ReflectionClass $class) {
        return self::get_url_class($class,false).'--'.$method->name.'.html';
    }
}