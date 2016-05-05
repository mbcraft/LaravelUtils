<?php

namespace Mbcraft\Laravel\Models;

/**
 * This interface must be implemented by model classes to be used with the entity controllers.
 */
interface INameable {
    
    /**
     * Returns the name of one entity of this model (singular).
     * Example : "invoice"
     * 
     * @return string The string name of one entity.
     */
    public static function one_entity();
    
    /**
     * Returns the string used to identify many entities of this model (plural).
     * Example : "invoices"
     * 
     * @return string The string name of many entities.
     */
    public static function many_entities();
    
}