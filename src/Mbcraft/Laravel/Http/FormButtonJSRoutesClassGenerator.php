<?php

namespace Mbcraft\Laravel\Http;

use Mbcraft\Piol\File;

use Mbcraft\Laravel\GeneratorUtils;

class FormButtonJSRoutesClassGenerator {
    
        const METHOD_DOCS = 
<<<METHOD_DOC
    /**
     * This method returns the javascript code used for routing post actions 
     * related to this method name.
     * If inside event attribute, use FALSE or no parameters, otherwise use TRUE
     * to get the 'javascript:' prefix to use the return value as direct link.
     * This must be used only inside forms.
     * 
     * @returns string The javascript code that triggers post, with or without 'javascript:' prefix.
     */
METHOD_DOC;
    
    private $routes;
    
    public function setRoutes($rt) {
        
        $this->routes = $rt;
    }
    
    private function loadRoutes() {
        $routes_cache = new File("/bootstrap/cache/routes.php");
        $routes_cache->includeFileOnce();
    }
    
    function regenerate_helper() {
        $this->loadRoutes();
        
        $rt = app('routes')->getRoutes();
        
        $collected_routes = [];
        
        foreach ($rt as $k) {
            if ($k instanceof \Illuminate\Routing\Route) {
                if ($k->getName() != null) {
                    $collected_routes[] = $k->getName();
                } else {
                    //echo "*** Route with missing name ***\n";
                }  
            }
        }
        
        $method_names = $this->getMethodNamesFromRoutes($collected_routes);
        
        $this->generateHelper($method_names);
    }
    
    private function getMethodNamesFromRoutes($collected_routes) {
        $method_names = [];
        foreach ($collected_routes as $route_key) {
            $method_name = GeneratorUtils::getMethodNameFromKey($route_key);
            if (!isset($method_names[$method_name])) {
                $method_names[$method_name] = $route_key;
            } else {
                $method_names["_".$method_name] = $route_key;
            }
        }
        return $method_names;
    }
    
    private function generateHelper($methods) {
        $class_content = $this->getClassOpening();
        foreach ($methods as $method_name => $route_key) {
            $class_content .= $this->getRouteMethod($method_name,$route_key);
        }
        $class_content .= $this->getClassClosing();
        
        $d = GeneratorUtils::getGeneratedClassFolder();
        $f = $d->newFile("FormButtonJSRoutes.php");
        $f->setContent($class_content);
    }
    
    private function getClassOpening($namespace = null) {
        $real_namespace = "";
        if ($namespace!=null)
            $real_namespace = "use ".$namespace.";";
        
        $result = <<<CLASS_OPENING_CODE
<?php
$real_namespace
    
/**
This class is generated with command artisan route:regenerate_helpers.
Manually editing is strongly discouraged.
*/
                
class FormButtonJSRoutes {
                                
    private static function __get_routing(\$method_name,\$route_key,\$args) {
    
        if (count(\$args)==0)
            return route(\$route_key);
        if (count(\$args)==1)
            return route(\$route_key,\$args[0]);
        if (count(\$args)==2)
            return route(\$route_key,\$args[0],\$args[1]);
        if (count(\$args)==3)
            return route(\$route_key,\$args[0],\$args[1],\$args[2]);
        if (count(\$args)==4)
            return route(\$route_key,\$args[0],\$args[1],\$args[2],\$args[3]);
        
        throw new \Exception(\$method_name . " : Too much parameters for route \$route_key .");   
    }
                
    private static function __get_javascript_post_routing(\$method_name,\$route_key,\$args) {
        return "this.form.action='".self::__get_routing(\$method_name,\$route_key,\$args)."';this.form.submit();"; 
    }
                
CLASS_OPENING_CODE;
        return $result;
    }
    
    private function getRouteMethod($method_name,$route_key) {
        $rlen = strlen($route_key);
        if ($route_key[$rlen-3]=='.' && $route_key[$rlen-2]=='d' && $route_key[$rlen-1]=='o') {
            $method_docs = self::METHOD_DOCS;
            $method_body = <<<METHOD_BODY
        \$all_args = func_get_args();
        if (count(\$all_args)>0 && \$all_args[0]===true) {
            \$args = array_shift(\$all_args);
            return "javascript:".self::__get_javascript_post_routing('$method_name','$route_key',\$args);
        } else {
            return self::__get_javascript_post_routing('$method_name','$route_key',\$all_args);
        }
METHOD_BODY;
        } else {
            return "";
        }
        $result = 
<<<METHOD_CODE

$method_docs           
    public static function $method_name() {      
$method_body
    }
                
METHOD_CODE;
        return $result;
    }
    
    private function getClassClosing() {
        $result = <<<CLASS_CLOSING_CODE
                
}
                
CLASS_CLOSING_CODE;
        return $result;
    }
}