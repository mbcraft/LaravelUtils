<?php

namespace Mbcraft\Laravel\Http;

/**
 * This class is useful for defining standard entity management routes.
 * The routes can be defined with a custom permission mask or with a preset mask.
 * The available actions are : index, create, edit, show, delete, confirm-delete
 * and restore.
 * 
 */
class RouteHelper {

    const INDEX_OPERATION = 0x0001;
    const CREATE_OPERATION = 0x0002;
    const EDIT_OPERATION = 0x0004;
    const SHOW_OPERATION = 0x0008;
    const DELETE_OPERATION = 0x0010;
    const CONFIRM_DELETE_OPERATION = 0x0020;
    const RESTORE_OPERATION = 0x0040;

    private static $route;
    
    public static function init($router) {
        self::$route = $router;
    }
    
    /**
     * Gets the operation mask resulting from the provided operation names.
     * 
     * @param array $op_names The array of operation names.
     * @return integer The operations mask
     */
    public static function getOperationsMaskByNames($op_names) {
        $operations_map = self::getOperationsMap();
        $operation_mask = 0;
        foreach ($op_names as $op) {
            $operation_mask |= $operations_map[$op];
        }
        return $operation_mask;
    }
    
    /**
     * Returns the list of all available operation names.
     * 
     * @return array The operation names as a string array.
     */
    public static function getOperationNames() {
        return array("index","create","edit","show","delete","confirm-delete","restore");
    }
    
    /**
     * Returns map of all the operations.
     * 
     * @return array The operations map (name => bitmask_value)
     */
    public static function getOperationsMap() {
        return array("index" => self::INDEX_OPERATION, "create" => self::CREATE_OPERATION, "edit" => self::EDIT_OPERATION, "show" => self::SHOW_OPERATION, "delete" => self::DELETE_OPERATION, "confirm-delete" => self::CONFIRM_DELETE_OPERATION, "restore" => self::RESTORE_OPERATION);
    }
    
    /**
     * Returns a mask for all the operations.
     * 
     * @return integer The operations mask
     */
    public static function getAllOperationsMask() {
        return self::INDEX_OPERATION | self::CREATE_OPERATION | self::EDIT_OPERATION | self::SHOW_OPERATION | self::DELETE_OPERATION | self::CONFIRM_DELETE_OPERATION | self::RESTORE_OPERATION;
    }
    
    /**
     * Returns a mask for all operations, except restore.
     * 
     * @return integer The operations mask
     */
    public static function getAllOperationsNoRestoreMask() {
        return self::INDEX_OPERATION | self::CREATE_OPERATION | self::EDIT_OPERATION | self::SHOW_OPERATION | self::DELETE_OPERATION | self::CONFIRM_DELETE_OPERATION;
    }
    
    /**
     * Returns a mask for all read only operations.
     * 
     * @return integer The operations mask
     */
    public static function getAllReadOperationsMask() {
        return self::INDEX_OPERATION | self::SHOW_OPERATION;
    }
    
    /**
     * Returns a mask for all the operations without the delete confirmation.
     * 
     * @return integer The operations mask
     */
    public static function getAllOperationsNoConfirmMask() {
        return self::INDEX_OPERATION | self::CREATE_OPERATION | self::EDIT_OPERATION | self::SHOW_OPERATION | self::DELETE_OPERATION | self::RESTORE;
    }
    
    /**
     * Returns a mask for all the editing operations (no create and delete).
     * 
     * @return integer The operations mask
     */
    public static function getAllReadAndEditOperationsMask() {
        return self::INDEX_OPERATION | self::EDIT_OPERATION | self::SHOW_OPERATION;
    }
    
    /**
     * Returns a mask for all the read and delete operations (no create, edit and restore).
     * 
     * @return type The operations mask
     */
    public static function getAllReadAndDeleteOperationsMask() {
        return self::INDEX_OPERATION | self::SHOW_OPERATION | self::DELETE_OPERATION | self::CONFIRM_DELETE_OPERATION;
    }
    
    /** 
     * Returns a mask for all the read only operations (index and show).
     * 
     * @return type The operations mask
     */
    public static function getAllReadOnlyOperationsMask() {
        return self::INDEX_OPERATION | self::SHOW_OPERATION;
    }
    
    /**
     * Declare the routes for the management of an entity.
     * 
     * @param string $singular The singular lowercase name of the entity, eg. : customer
     * @param string $plural The plural lowercase name of the entity, eg. : customers
     * @param string $controller_strict_name The strict path of the controller, eg. : Admin/Users
     * @param integer $operation_mask The masks of all the operations allowed.
     * @param function $more_to_do A function to execute to declare more routes inside this group.
     */
    public static function declareGuardedEntityManagement($singular, $plural, $controller_strict_name, $operation_mask,$operation_names_prefix="", $nested_routes = null) {

        $operation_names_prefix = $operation_names_prefix == "" ? $operation_names_prefix : $operation_names_prefix.".";
        $controller_strict_name =  str_replace("/", "\\", $controller_strict_name);
        
        # User Management
        self::$route->group(array('prefix' => $plural, 'before' => 'Sentinel'), function () use ($singular, $plural, $controller_strict_name, $operation_mask, $operation_names_prefix, $nested_routes) {

            if (($operation_mask & self::INDEX_OPERATION) == self::INDEX_OPERATION) {
                self::$route->get('/', array('as' => $operation_names_prefix.$plural.'.index', 'uses' => $controller_strict_name . 'Controller@getIndex'));
            }
            if (($operation_mask & self::CREATE_OPERATION) == self::CREATE_OPERATION) {
                self::$route->get('create', array('as' => $operation_names_prefix.$plural . '.create', 'uses' => $controller_strict_name . 'Controller@getCreate'));
                self::$route->post('create', array('as' => $operation_names_prefix.$plural . '.create.do', 'uses' => $controller_strict_name . 'Controller@postCreate'));   
            }
            if (($operation_mask & self::EDIT_OPERATION) == self::EDIT_OPERATION) {
                self::$route->get('{' . $singular . 'Id}/edit', array('as' => $operation_names_prefix.$plural . '.edit', 'uses' => $controller_strict_name . 'Controller@getEdit'));
                self::$route->post('edit', array('as' => $operation_names_prefix.$plural . '.edit.do', 'uses' => $controller_strict_name . 'Controller@postEdit'));
            }
            if (($operation_mask & self::DELETE_OPERATION) == self::DELETE_OPERATION) {
                self::$route->post('delete', array('as' => $operation_names_prefix.$plural . '.delete.do', 'uses' => $controller_strict_name . 'Controller@postDelete'));
            }
            if (($operation_mask & self::CONFIRM_DELETE_OPERATION) == self::CONFIRM_DELETE_OPERATION) {
                self::$route->get('{' . $singular . 'Id}/confirm-delete', array('as' => $operation_names_prefix.$plural . '.confirm-delete', 'uses' => $controller_strict_name . 'Controller@getModalDelete'));
            }
            if (($operation_mask & self::RESTORE_OPERATION) == self::RESTORE_OPERATION) {
                self::$route->post('restore', array('as' => $operation_names_prefix.$plural . '.restore.do', 'uses' => $controller_strict_name . 'Controller@postRestore'));
            }
            if (($operation_mask & self::SHOW_OPERATION) == self::SHOW_OPERATION) {
                self::$route->get('{' . $singular . 'Id}', array('as' => $operation_names_prefix.$plural . '.show', 'uses' => $controller_strict_name . 'Controller@getShow'));
            }
            
            if ($nested_routes!=null)
                $nested_routes($singular, $plural, $controller_strict_name, $operation_mask, $operation_names_prefix);
            
        });
    }

}

