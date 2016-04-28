<?php

namespace Mbcraft\Laravel\Http;

/**
 * This class contains HTTP errors responses to be returned in case of errors.
 * The views are taken from errors/<error_number> path. A message parameter can 
 * also be specified and passed to the rendered view.
 * 
 */
class HttpError {

    public static function unauthorized($message=null) {
        return self::errorViewOrException(401, $message);
    }
    
    public static function forbidden($message=null) {
        return self::errorViewOrException(403, $message);
    }
    
    public static function not_found($message=null) {
        return self::errorViewOrException(404, $message);
    }
    
    public static function internal_server_error($message=null) {
        return self::errorViewOrException(500, $message);
    }
    
    public static function not_implemented($message=null) {
        return self::errorViewOrException(501, $message);
    }
    
    public static function service_unavailable($message=null) {
        return self::errorViewOrException(503, $message);
    }
    
    private static function errorViewOrException($error,$message) {
        if (view()->exists("errors/".$error)) {
            return response()->view("errors/".$error,["message" => $message],$error);
        } 
        else
            throw new Exception("View for error ".$error." not found.");
            
    }
}