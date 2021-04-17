<?php
namespace objmdl;

class Scope
{
    private $reference = null ;

    public function __construct(OObject $object) {
        $this->reference = $object ;
    }

    /**
     * Gets the referenced object.
     */
    public function get_ref():OObject {
        return $this->reference ;
    }
}