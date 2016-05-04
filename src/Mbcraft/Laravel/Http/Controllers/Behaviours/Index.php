<?php

namespace Mbcraft\Laravel\Http\Controllers\Behaviours;

use Mbcraft\Laravel\Http\Controllers\QueryFilters\QueryFilterFactory;

use Input;

/**
 * This trait is used to get the index behaviour out of an entity controller.
 * It adds the getIndex method for showing the entities.
 */
trait Index {
        
    /**
     * Returns the page for the entity index form.
     */
    public function getIndex($filters = array()) {
        
        $model_class = self::MODEL_CLASS;
        
        $current_query = $this->getSummaryCustomQuery($model_class,$filters); 

        // Checking filters ?
        // ..... not implemented
        
        // Applying filters (if any)
        if (isset($this->import_select_filters)) {
            foreach ($this->import_select_filters as $f => $f_spec) {   
                if (Input::has($f)) {
                    $filters[] = QueryFilterFactory::{$f_spec}($f,Input::get($f));
                }
            }
        }
        
        $current_query = QueryFilterFactory::applyAll($current_query,$filters);
        
        // Do we want to include the deleted customers?
        if (Input::get('withDeleted')) {
            $entities = $current_query->withTrashed()->get();
        } elseif (Input::get('onlyDeleted')) {
            $entities = $current_query->onlyTrashed()->get();
        } else {
            // Grab all the entities
            $entities = $current_query->get();
        }
        
        $one_entity = $model_class::one_entity();
        $many_entities = $model_class::many_entities();
                
        $entity_ref = $model_class::many_entities();
        
        $$entity_ref = $entities;
        
        $entity_params = compact($model_class::many_entities(),"one_entity","many_entities");
        
        foreach ($filters as $flt) {
            $entity_params[$flt->getKey()] = $flt->getValue();
        }
        
        $view_params = array_merge($entity_params,$this->getSummaryAdditionalEntities());
        
        //show the select for the elements - this is used for showing the 
        // select control inside the form with all the needed data
        // no external layout is involved
        if (Input::has("select") && $this->select_from_index===TRUE) {
            return $this->getViewFor('select_from_index', $view_params);
        } else {
            // Show the index page for this entity
            return $this->getViewFor('index', $view_params);
        }
    }
    
    /**
     * Hook method to override for listing the supported filters and their parameters
     * used in query builder.
     * 
     * This is used basically to check the allowed filters before the are actually applied
     */
    /*
    protected function getSupportedModelFilters() {
        return array();
    } 
    */
}