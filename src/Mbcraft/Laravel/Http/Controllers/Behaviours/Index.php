<?php

namespace Mbcraft\Laravel\Http\Controllers\Behaviours;

use Mbcraft\Laravel\Http\Controllers\QueryFilters\QueryFilterFactory;

use Mbcraft\Laravel\Misc\UsageHelper;
use Input;

/**
 * This trait is used to get the index behaviour out of an entity controller.
 * It adds the getIndex method for showing the entities.
 */
trait Index {
        
    /**
     * Returns the page for the entity index form.
     *
     * @param array filters The list of filters to be applied to the query for the index
     * @param string view The view name, defaults to 'index'.
     *
     * @return View The view to show
     */
    public function getIndex($filters = array(),$view_name = 'index') {
        
        $model_class = self::MODEL_CLASS;
        
        $current_query = $this->getSummaryCustomQuery($model_class,$filters); 

        // Checking filters ?
        // ..... not implemented
        
        // Applying filters (if any)
        if (isset($this->import_select_filters)) {
            foreach ($this->import_select_filters as $f => $f_spec) {   
                if (Input::has($f)) {
                    $filters[] = QueryFilterFactory::$f_spec($f,Input::get($f));
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

        //ajax view, maybe will be fixed in the future ...
        //show the select for the elements - this is used for showing the 
        // select control inside the form with all the needed data
        // no external layout is involved
        if (Input::has("select") && $this->select_from_index===true) {
            UsageHelper::deprecated(Input::has('select'), 'input', 'select', 'ajax');
            UsageHelper::deprecated($this->select_from_index, 'controller variable', 'select_from_index', 'enable_ajax_index');
            \Log::debug('Use dedicated controller method.');

            $view_name = 'select_from_index';
        }

        // Return the right view with its parameters
        
        return $this->getViewFor($view_name, $view_params);

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