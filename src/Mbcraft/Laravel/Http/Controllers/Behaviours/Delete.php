<?php

namespace Mbcraft\Laravel\Http\Controllers\Behaviours;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use View;
use Input;

/**
 * This trait is used to get the delete behaviour on an entity controller.
 * It defines two methods :
 * 
 * getModalDelete : returns the modal dialog
 * postDelete : actually executes the delete
 */
trait Delete {

    /**
     * Delete Confirm modal dialog
     *
     * @param   int $id Entity id to ask deletion confirmation
     * @return  View The modal delete view
     */
    public function getModalDelete($id = null) { 
        $model_class = self::MODEL_CLASS;
        $model = $model_class::many_entities();

        $confirm_route = $this->getRouteFor('delete.do');
        $entity_id = $id;
        
        return View::make('admin/layouts/modal_confirmation', compact('model', 'confirm_route','entity_id'));
    }

    /**
     * Adds the postDelete method to the controller for the given entity
     * Requires an id input parameter which identifies the entity
     *
     * @return Redirect The redirect to follow after the delete operation.
     */
    public function postDelete() {

        $model_class = self::MODEL_CLASS;
        $id = Input::get('id');
        
        try {
            // Get entity 
            $entity = $this->getDetailsCustomQuery(false,$model_class,$id);
            
        } catch (ModelNotFoundException $e) {
            // Prepare the error message
            $error = $this->LMessage->{$model_class::one_entity()."_not_found"}(compact("id"));

            // Redirect to the entity index page with an error
            return $this->getRedirectFor('index')->with('error', $error);
        }

        $before_delete_result = $this->beforeEntityDelete($entity);

        if ($before_delete_result != null) {
            return $before_delete_result;
        }

        try {
            // Delete the entity
            //to allow soft delete, we are performing query on entity model 
            $entity->delete();
        } catch (Exception $ex) {
            // Prepare the success message
            $error = $this->LMessage->error_deleted();

            // Redirect to the index entity page with an error message
            return $this->getRedirectFor('index')->with('error', $error);
        }

        \Log::debug("Entity deleted succesfully.");

        $after_delete_result = $this->afterEntityDelete($entity);
        if ($after_delete_result != null) {
            return $after_delete_result;
        }

        // Prepare the success message
        $success = $this->LMessage->success_deleted();

        // Redirect to the entity index page with a success message
        return $this->getRedirectFor('index')->with('success', $success);
    }

    /**
     * Default behavior for 'before entity delete' hook.
     */
    protected function defaultBeforeEntityDelete($entity) {
        return null;
    }
    
    /**
     * Hook method called before the entity is deleted.
     * 
     */
    protected function beforeEntityDelete($entity) {
        return $this->defaultBeforeEntityDelete($entity);
    }
    
    /**
     * Default behavior for 'after entity delete' hook.
     */
    protected function defaultAfterEntityDelete($entity) {
        return null;
    }

    /**
     * Hook method called after the entity is deleted.
     */
    protected function afterEntityDelete($entity) {
        return $this->defaultAfterEntityDelete($entity);
    }

}
