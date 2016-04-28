<?php

namespace Mbcraft\Laravel\Http\Controllers\Behaviours;

use Redirect;
use Validator;
use View;
use Input;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * This trait is used to add the edit behaviour in an entity controller.
 */
trait Edit {

    /**
     * Adds the getEdit method to the controller for showing the entity for editing.
     *
     * @param  int $id The id of the entity to show for the editing
     * 
     * @return View
     */
    public function getEdit($id = null) {

        $model_class = self::MODEL_CLASS;
        
        try {
            //\Log::debug("Looking for entity id : " . $id);
            // Get the entity by id
            $entity = $this->getDetailsCustomQuery(false,$model_class,$id);

            //\Log::debug("Entity with id : " . $entity->id);
        } catch (ModelNotFoundException $e) {
            // Prepare the error message
            $error = $this->LMessage->{$model_class::one_entity().'_not_found'}(compact('id'));

            // Redirect to the entity index page with an error message
            return $this->getRedirectFor('index')->with('error', $error);
        }

        $entity_ref = $model_class::one_entity();

        $$entity_ref = $entity;
        
        $view_params = array_merge(compact($model_class::one_entity()),$this->getDetailsAdditionalEntities());
        
        // Show the page
        return $this->getViewFor('edit', $view_params);
    }

    /**
     * Adds the postEdit method to the entity controller for saving the changes to the entity
     *
     * @param  int $id The id of the entity to edit
     * @return Redirect
     */
    public function postEdit() {
        
        \Log::debug("Processing edit request ...");
        
        $model_class = self::MODEL_CLASS;
        
        $id = Input::get('id');
        
        try {
            // Get the entity
            $entity = $model_class::findOrFail($id);
        } catch (ModelNotFoundException $e) {

            // Prepare the error message
            $error = $this->LMessage->{$model_class::one_entity().'_not_found'}(compact('id'));

            // Redirect to the entity index page with an error message
            return $this->getRedirectFor('index')->with('error', $error);
        }
        // Gets the prepared validation rules
        $validationRules = $this->getPreparedValidationRules($entity->getAttributes());
        
        \Log::debug("Validating data ...");
        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $validationRules);
        \Log::debug("Validation successfull!"); 
        // If validation fails, we'll exit the operation now.
        if ($validator->fails()) {
            // Ooops.. something went wrong
            return Redirect::back()->withInput()->withErrors($validator);
        }

        $p_edit_data = [];

        foreach (Input::all() as $field => $value) {
            if (isset($validationRules[$field])) {
                $p_edit_data[$field] = $value;
            }
        }

        list($before_edit_result, $edit_data) = $this->beforeEntityEdit($p_edit_data, $entity);

        if ($before_edit_result != null) {
            return $before_edit_result;
        }

        //save the readed data into the entity fields
        foreach ($edit_data as $field => $value) {
            $entity->{$field} = $edit_data[$field];
        }

        try {
            // Save the entity
            $result = $entity->save();
        } catch (Exception $ex) {
            // Prepare the success message
            $error = $this->LMessage->error_updated();

            // Redirect to the entity index page with an error message
            return $this->getRedirectFor('index')->with('error', $error);
        }

        // Was the entity updated?
        if ($result) {

            \Log::debug("Entity edited successfully.");

            $after_edit_result = $this->afterEntityEdit($entity);
            if ($after_edit_result != null) {
                return $after_edit_result;
            }

            // Prepare the success message
            $success = $this->LMessage->success_updated();

            // Redirect to the entity index page with a success message
            return $this->getRedirectFor('index')->with('success', $success);
        } else {

            // Prepare the error message
            $error = $this->LMessage->error_updated();

            // Redirect to the entity index page with all the input parameters and an error message
            return $this->getRedirectFor('index')->withInput()->with('error', $error);
        }
    }
    
    /**
     * Default behavior for 'before entity edit'.
     */
    protected function defaultBeforeEntityEdit($edit_data, $entity) {
        return array(null, $edit_data);
    }

    /**
     * Hook method for adding or altering the edit data/entity before save
     */
    protected function beforeEntityEdit($edit_data, $entity) {
        return $this->defaultBeforeEntityEdit($edit_data, $entity);
    }
    
    /**
     * Default behavior for 'after entity edit'.
     */
    protected function defaultAfterEntityEdit($entity) {
        return null;
    }

    /**
     * Hook method called if save was succesfull
     */
    protected function afterEntityEdit($entity) {
        return $this->defaultAfterEntityEdit($entity);
    }

}