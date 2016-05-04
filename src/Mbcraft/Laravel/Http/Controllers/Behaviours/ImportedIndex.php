<?php

namespace Mbcraft\Laravel\Http\Controllers\Behaviours;

/**
 * This trait is used to get the index behaviour out of a controller.
 * Useful for showing entities related to an other entity. (eg. products in a given category, where the 
 * category is the current entity).
 * It can be used multiple times inside an entity, if an entity is related to many entities.
 * Eg. A "person" can import indexes to show "pictures", "visited_places" and "seen_films" that are related to it inside its 'PersonController'.
 */
trait ImportedIndex {
    
    /**
     * Returns the page for the entity index form.
     * All received parameters are passed to the controller action method.
     */
    public function getImportedIndex($controller_class,$parameters = array()) {
        
        $controller_instance = new $controller_class();
                
        return $controller_instance->getIndex($parameters);
    }
}