<?php

namespace Mbcraft\Laravel\Http\Controllers\Behaviours;

/**
 * This trait is used to get the index behaviour out of a controller.
 * Useful for showing entities related to an other entity. (eg. products in a given category, where the 
 * category is the current entity).
 */
trait ImportedIndex {
    
    /**
     * Returns the page for the entity index form.
     */
    public function getImportedIndex($controller_class,$parameters = array()) {
        
        $controller_instance = new $controller_class();
                
        return $controller_instance->getIndex($parameters);
    }
}