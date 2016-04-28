<?php

namespace Mbcraft\Laravel\Http\Controllers\Behaviours;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Input;
/**
 * This trait is used to get the show behaviour out of a controller.
 * It adds the getShow method to the entity.
 */
trait Show {

    /**
     * Returns the show entity view.
     * Requires the id of the entity.
     */
    public function getShow($id = null) {
        
        $model_class = self::MODEL_CLASS;

        try {

            $entity = $this->getDetailsCustomQuery(false,$model_class,$id);
            
        } catch (ModelNotFoundException $ex) {

            // Prepare the error message
            $error = $this->LMessage->{$model_class::one_entity().'_not_found'}(compact('id'));

            // Redirect to the entities index page with an error message
            return $this->getRedirectFor('index')->with('error', $error);
        }

        $entity_ref = $model_class::one_entity();

        $$entity_ref = $entity;
        
        $view_params = array_merge(compact($model_class::one_entity()),$this->getDetailsAdditionalEntities());
        

        if (Input::has('select') && $this->select_from_show===TRUE)
            //select from show is used for showing the entity details with ajax,
            //so no external layout is involved
            return $this->getViewFor('select_from_show', $view_params);
        else
            //uses external layout to show the entity
            return $this->getViewFor('show', $view_params);
    }

}
