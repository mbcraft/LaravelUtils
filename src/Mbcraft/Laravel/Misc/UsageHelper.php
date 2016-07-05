<?php
/**
 * Created by PhpStorm.
 * User: marco
 * Date: 05/07/16
 * Time: 14.39
 */
namespace Mbcraft\Laravel\Misc;

class UsageHelper {

    /**
     * Logs the deprecated usage of something in the code, suggesting something to replace with.
     *
     * @param $condition The condition that triggers the deprecation message
     * @param $area The area about the deprecation code
     * @param $old_param The old parameter name
     * @param $new_param The new parameter name
     */
    static function deprecated($condition,$area,$old_param,$new_param) {
        if ($condition) \Log::debug("Using DEPRECATED '".$old_param."' in ".$area.", use ".$new_param." instead.");
    }
    
}