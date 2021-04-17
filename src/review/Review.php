<?php

/**
* Review
*/
class Review
{
    static public function get_class($classname)
    {
        $classname = '\\'.ltrim($classname,'\\');
        Msg::debug($classname);
        try {
            $the_class = new \ReflectionClass($classname);
            return $the_class;
        }
        catch (Exception $ex) { debug_print_backtrace(); }
    }

    static public function get_methods(ReflectionClass $class)
    {
        $methods=[];
        $static_methods=[];

        foreach ($class->getMethods() as $method) {
            if (! $method->isPublic()) continue;
            if ($method->isStatic()) $static_methods[] = $method;
            else $methods[]=$method;
        }
        usort($methods, function($a,$b) use ($class)
        {
            if ($a->getDeclaringClass()->name != $b->getDeclaringClass()->name) {
                return $class->name == $a->getDeclaringClass()->name ? -1 : 1;
            }
            return $a->name<=>$b->name;
        });
        usort($static_methods, function($a,$b) use ($class)
        {
            if ($a->getDeclaringClass()->name != $b->getDeclaringClass()->name) {
                return $class->name == $a->getDeclaringClass()->name ? -1 : 1;
            }
            return $a->name<=>$b->name;
        });
        return [$methods, $static_methods];
    }

    static public function get_method_meta(ReflectionMethod $method, ReflectionClass $class)
    {
        $pfx=[];
        if ($method->isStatic()) $pfx[] = 'static' ;
        if ($method->isPublic()) $pfx[] = 'public' ;
        if ($method->isConstructor()) $pfx[] = 'constructor' ;
        if (substr($method->name,0,2)=='__') $pfx[] = '__magic' ;
        if ($method->getDeclaringClass() != $class) $pfx[] = 'inherited';
        return $pfx;
    }

    static public function get_method_fqn(ReflectionMethod $method, ReflectionClass $class)
    {
    }

}