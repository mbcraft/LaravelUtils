<?php

namespace Mbcraft\Laravel\Models;

/**
 * This interface must be implemented by model classes to be used with the entity controllers.
 */
interface INameable {
    
    /**
     * Returns the name of one entity of this model (singular).
     * If the name consist of more than one word, words must be underscore separated.
     * Example : "invoice", "system_notification"
     * 
     * @return string The string name of one entity.
     */
    public static function one_entity();
    
    /**
     * Returns the string used to identify many entities of this model (plural).
     * If the name consist of more than one word, words must be underscore separated.
     * Example : "invoices", "system_notifications"
     * 
     * @return string The string name of many entities.
     */
    public static function many_entities();
    
}