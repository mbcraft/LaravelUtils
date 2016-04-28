<?php

namespace Mbcraft\Laravel\Database;

/**
 * This trait enables to empty and set auto_increment to 1 on a list of tables.
 * 
 * Include this trait in your class, then define the private variable 'reset_tables'
 * putting all the tables to be reset inside an array.
 * The AUTO_INCREMENT counter will also be reset to 1.
 * 
 */
trait ResetTablesSeederTrait {
           
    /**
     * Call this method to reset all tables listed in $reset_tables field.
     */
    protected function flushResets() {
        
        if (!isset($this->reset_tables))
            throw new \Exception("The field reset_tables must be set for using flushResets and ResetTablesTrait.");
        
        foreach ($this->reset_tables as $table_name) {
            \DB::table($table_name)->delete();
        }
        
        foreach ($this->reset_tables as $table_name) {
            \DB::statement("ALTER TABLE ".$table_name." AUTO_INCREMENT=1;");
        }
    }
}  