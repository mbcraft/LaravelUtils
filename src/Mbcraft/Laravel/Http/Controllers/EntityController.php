<?php

namespace Mbcraft\Laravel\Http\Controllers;

use App\Http\Controllers\Controller;

use Redirect;
use View;
use URL;

use Illuminate\Support\MessageBag;

/**
 * This class must be used as a base class for using the available traits.
 * It implements resource management for a single model class.
 */
class EntityController extends Controller {

    /**
     * Message bag.
     *
     * @var MessageBag
     */
    protected $messageBag = null;

    /**
     * Initializer.
     *
     * @return void
     */
    public function __construct() {
        // CSRF Protection
        $this->beforeFilter('csrf', array('on' => 'post'));

        //
        $this->messageBag = new MessageBag;
    }
    
    protected function getRedirectFor($action) {
        $model_class = static::MODEL_CLASS;
        if (defined("static::ROUTE_MANY"))
            $route_many = static::ROUTE_MANY;
        else {
            $route_many = $model_class::many_entities();
        }
        return Redirect::route("admin.".$route_many.".".$action);
    }
    
    protected function getRouteFor($action,$params = array()) {
        $model_class = static::MODEL_CLASS;
        if (defined("static::ROUTE_MANY"))
            $route_many = static::ROUTE_MANY;
        else {
            $route_many = $model_class::many_entities();
        }
        return URL::route("admin.".$route_many.".".$action,$params);
    }
    
    protected function getViewFor($action,$params = array()) {
        
        $model_class = static::MODEL_CLASS;
        $many_entities = $model_class::many_entities();
        $params["many_entities_route"] = self::many_entities_route();
        $params["one_entity_route"] = self::one_entity_route();
        
        return View::make("admin.".$many_entities.".".$action,$params);
    }
    
    /**
     * Replaces all the occurrences of the columns values with the values from this instance.
     * The syntax to use inside the validation rules is : {column_name} .
     * Eg. : {id}  becomes  15  if this model instance has id equal to 15.
     * 
     * @return array The validation rules with all the custom values replaced from this model instance.
     */
    protected final function getPreparedValidationRules($params) {
        $rules = $this->validationRules;
        
        $prepared_rules = array();
        
        foreach ($rules as $r_key => $r_value) {
            foreach ($params as $att_name => $att_value) {
                $r_value = str_replace("{".$att_name."}", $att_value, $r_value);
            }
            //replaces the {id}s with NULL if we are actually creating a new model.
            if (!isset($rules["id"]))
                $r_value = str_replace("{id}", "NULL", $r_value);
            
            $prepared_rules[$r_key] = $r_value;
        }
        
        return $prepared_rules;
    }
    
    public static final function one_entity_route() {
        $model_class = static::MODEL_CLASS;
        $name = $model_class::one_entity();
        $name_parts = explode('_',$name);
        $name_start = array_shift($name_parts);
        $other_part = join("_", $name_parts);
        $last_part = ucwords($other_part,'_');
        return $name_start.$last_part;
    }
    
    public static final function many_entities_route() {
        $model_class = static::MODEL_CLASS;
        $name = $model_class::many_entities();
        $name_parts = explode('_',$name);
        $name_start = array_shift($name_parts);
        $other_part = join("_", $name_parts);
        $last_part = ucwords($other_part,'_');
        return $name_start.$last_part;
    }
    
    protected function getSummaryCustomQuery($model_class,$filters) {
        return $model_class::query();
    }
    
    protected function getDetailsCustomQuery($deleted,$model_class,$id) {
        $query = $model_class::query();
        if ($deleted) {
            $query = $query->withTrashed();
        }
        return $query->findOrFail($id);
    }
    
    protected function getSummaryAdditionalEntities() {
        return array();
    }
    
    protected function getDetailsAdditionalEntities() {
        return array();
    }
}