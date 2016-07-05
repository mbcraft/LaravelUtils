<?php

namespace Mbcraft\Laravel\Http\Controllers\Behaviours;

use Redirect;
use Input;
use Validator;

/**
 * This trait is used to get the create behaviour on an entity controller.
 * It defines two methods : getCreate and postCreate.
 * It also provides two methods for tuning the entity creation process :
 * beforeEntityCreate and afterEntityCreate.
 */
trait Create {

    /**
     * Adds the getCreate method to the controller for showing the page for collecting
     * all the data required for creating an entity.
     * 
     * Returns the page for the entity create form.
     *
     * @param string $view_name The view name to use, defaults to 'create'.
     * @return View The created view
     */
    public function getCreate($view_name='create') {

        $view_params = array_merge(Input::all(),$this->getDetailsAdditionalEntities());
        
        // Return the right view with its parameters

        return $this->getViewFor($view_name, $view_params);

    }
    
    /**
     * Adds the postCreate method to the controller for creating entities with all
     * the required parameters submitted with a form.
     *
     * @return Redirect The redirect to follow after the operation
     */
    public function postCreate() {
        $validationRules = $this->getPreparedValidationRules(Input::all());
        
        $model_class = self::MODEL_CLASS;
        
        \Log::debug("Validating new " . $model_class::one_entity() . " ...");
        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $validationRules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails()) {
            \Log::debug("Validation failed");
            // Ooops.. something went wrong
            return Redirect::back()->withInput()->withErrors($validator);
        }

        $p_create_data = [];
        foreach (Input::all() as $key => $value) {
            if (isset($validationRules[$key])) {
                $p_create_data[$key] = $value;
            }
        }

        list($before_create_result, $create_data) = $this->beforeEntityCreate($p_create_data);

        if ($before_create_result != null) {
            return $before_create_result;
        }

        try {
            // Create the entity
            $entity = $model_class::create($create_data);
        } catch (Exception $ex) {
            // Prepare the error message
            $error = $this->LMessage->error_created();

            // Redirect to the entity index page with all the input parameters and an error message
            return $this->getRedirectFor('index')->withInput()->with('error', $error);
        }
        \Log::debug("Entity creation successful");

        $after_create_result = $this->afterEntityCreate($entity);
        if ($after_create_result != null) {
            return $after_create_result;
        }

        // Redirect to the index page with success message
        return $this->getRedirectFor('index')->with('success', $this->LMessage->success_created());
    }
    
    /**
     * Default behavior for 'before entity create' hook.
     * 
     */
    protected function defaultBeforeEntityCreate($create_data) {
        return array(null,$create_data);
    }

    /**
     * Hook method called before entity creation with all the fetched input data.
     */
    protected function beforeEntityCreate($create_data) {
        return $this->defaultBeforeEntityCreate($create_data);
    }

    /**
     * Default behavior for 'after entity create' hook.
     * 
     */
    protected function defaultAfterEntityCreate($entity) {
        return null;
    }
    
    /**
     * Hook method called after the entity is created.
     */
    protected function afterEntityCreate($entity) {
        return $this->defaultAfterEntityCreate($entity);
    }

}
