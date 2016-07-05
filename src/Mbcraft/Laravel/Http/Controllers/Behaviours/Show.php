<?php

namespace Mbcraft\Laravel\Http\Controllers\Behaviours;

use Mbcraft\Laravel\Misc\UsageHelper;
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
     *
     * @param int id The id of the entity to show
     * @param string $view_name The name of the view, defaults to 'show'.
     */
    public function getShow($id = null,$view_name = 'show') {
        
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

        //ajax view, maybe will be fixed in the future ...
        if (Input::has('select') && $this->select_from_show===true) {
            UsageHelper::deprecated(Input::has('select'), 'Input','select', 'ajax');
            UsageHelper::deprecated($this->select_from_show, 'controller variable' ,'select_from_show', 'enable_ajax_show');
            \Log::debug('Use dedicated controller method.');
            //select from show is used for showing the entity details with ajax,
            //so no external layout is involved
            $view_name = 'select_from_show';
        }

        // Return the right view with its parameters
        
        return $this->getViewFor($view_name, $view_params);

    }

}
