<?php

namespace Mbcraft\Laravel\Models;

interface INameable {
    
    /**
     * Returns the name of one entity used in controllers and views.
     * 
     * @return string The name of one entity
     */
    public static function one_entity();
    
    /**
     * Ritorna il nome di molte entità.
     * 
     * @return string Ritorna il nome di molte entità come stringa, 
     *      in questo caso "spots".
     */
    public static function many_entities();
    
}