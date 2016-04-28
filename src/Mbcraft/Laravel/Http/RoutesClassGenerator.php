<?php

namespace Mbcraft\Laravel\Http;

use Mbcraft\Piol\File;

use Mbcraft\Laravel\GeneratorUtils;

class RoutesClassGenerator {
                    
    const METHOD_DOCS = 
<<<METHOD_DOC
    /**
     * This method returns the url to be used inside links.
     *
     * @returns string The url that sends the browser to the location related to this method. 
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
        $f = $d->newFile("Routes.php");
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
                
class Routes {
                                
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
                
CLASS_OPENING_CODE;
        return $result;
    }
    
    private function getRouteMethod($method_name,$route_key) {

        $method_docs = self::METHOD_DOCS;
        $method_body = "        return self::__get_routing('$method_name','$route_key',func_get_args());";
        
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