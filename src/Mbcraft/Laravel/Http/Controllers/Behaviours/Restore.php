<?php

namespace Mbcraft\Laravel\Http\Controllers\Behaviours;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Input;

/**
 * This trait is used to get the restore behaviour out of an entity controller.
 * It adds the postRestore method for restoring the entity.
 */
trait Restore {

    /**
     * Restore a deleted entity.
     *
     * @param  int      $id
     * @return Redirect
     */
    public function postRestore() {

        $model_class = self::MODEL_CLASS;
        $id = Input::get('id');

        try {

            // Get the trashed entity
            $entity = $this->getDetailsCustomQuery(true,$model_class,$id);
            
        } catch (ModelNotFoundException $e) {

            // Prepare the error message
            $error = $this->LMessage->{$model_class::one_entity().'_not_found'}(compact('id'));

            // Redirect to the entities management page
            return $this->getRedirectFor('index')->with('error', $error);
        }

        $before_restore_result = $this->beforeEntityRestore($entity);
        if ($before_restore_result != null) {
            return $before_restore_result;
        }
        // Restore the entity
        try {
            $entity->restore();
        } catch (Exception $ex) {
            // Prepare the success message
            $error = $this->LMessage->error_restored();

            // Redirect to the entity management page
            return $this->getRedirectFor('index')->with('error', $error);
        }

        \Log::debug("Entity restored successfully.");

        $after_restore_result = $this->afterEntityRestore($entity);
        if ($after_restore_result != null) {
            return $after_restore_result;
        }

        // Prepare the success message
        $success = $this->LMessage->success_restored();

        // Redirect to the entity management page
        return $this->getRedirectFor('index')->with('success', $success);
    }

    /**
     * Default behavior for 'before entity restore' hook.
     */
    protected function defaultBeforeEntityRestore($entity) {
        return null;
    }
    
    /**
     * Hook method called before entity restore.
     */
    protected function beforeEntityRestore($entity) {
        return $this->defaultBeforeEntityRestore($entity);
    }

    /**
     * Default behavior for 'after entity restore' hook.
     */
    protected function defaultAfterEntityRestore($entity) {
        return null;
    }
    
    /**
     * Hook method called after entity restore.
     */
    protected function afterEntityRestore($entity) {
        return $this->defaultAfterEntityRestore($entity);
    }

}
