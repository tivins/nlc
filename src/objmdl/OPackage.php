<?php
namespace objmdl;
use \parse\Token;
/**
 * A package (also known as “namespace”) contains severals classes, or sub-packages.
 * A package protects against the conflicts of the classes' and functions' names.
 */
class OPackage extends OObject
{
    private $packages = [] ;
    private $classes = [] ;
    public $package = null ;

    /**
     * Create a new Package object.
     *
     * @param $package
     *      The parent package or null if the package is a root package.
     */
    public function __construct(Token $root_token, OPackage $package = null)
    {
        parent::__construct() ;
        $this->root_token = $root_token ;
        $this->package = $package ;
    }

    public function get_packages():Array
    {
        return $this->packages ;
    }

    public function get_classes():Array
    {
        return $this->classes ;
    }

    /**
     * Gets a sub-package of the current package or null if the wanted package
     * was not found.
     *
     * @return  Returns the subpackage if found, or null otherwise.
     */
    public function get_package($packname) /* :?OPackage */
    {
        if (!isset($this->packages[$packname])) return null ;
        return $this->packages[$packname] ;
    }

    /**
     * Adds the given package to the list of subpackage of the package.
     *
     * @return  Returns true if the package was added, or false otherwise.
     */
    public function add_package(OPackage $pack):bool
    {
        if (isset($this->packages[$pack->name])) {
            Msg::error('The package "'.$pack->name.'" already exists in the object.');
            return false;
        }
        $this->packages[$pack->name] = $pack ;
        return true;
    }

    /**
     * Adds the given class to the list of classes of the package.
     *
     * @return  Returns true if the class was added, or false otherwise.
     */
    public function add_class(OClass $class):bool
    {
        if (isset($this->classes[$class->name]))
        {
            Msg::error('[todo] this class already exists for this package.') ;
            return false ;
        }
        $this->classes[$class->name] = $class ;
        return true;
    }


    public function get_fq_chain():array
    {
        $packages = [] ;
        $cur = $this ;
        while ($cur) {
            array_unshift($packages, $cur) ;
            $cur = $cur->package ;
        }
        array_shift($packages);// remove global.
        return $packages ;
    }

    public function get_fqn():string
    {
        $packs = $this->get_fq_chain() ;
        return implode('.', array_column($packs, 'name')) ;
    }
}