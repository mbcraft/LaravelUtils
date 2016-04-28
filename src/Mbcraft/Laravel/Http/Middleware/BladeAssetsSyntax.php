<?php

namespace Mbcraft\Laravel\Http\Middleware;

use Closure;
use Blade;

/**
 * This middleware adds new directives for managind the css and js resource
 * loading for improved html experience.
 * 
 * @require_remote_js : requires a remote javascript file
 * @require_local_js : requires a local javascript file
 * @require_remote_css : requires a remote css file
 * @require_local_css : requires a local css file
 */
class BladeAssetsSyntax extends __AbstractBladeSyntax {
    
    /**
     * Blade directive for loading the remote js with the given key 
     * inside the css requirements array.
     * 
     * @return string Nothing to output.
     */
    private function setupRequireRemoteJs() {
        Blade::directive('require_remote_js',function($expression) {
            
            $key = $this->stringParamAsString($expression);
            
            $result = <<<TEXT
            <?php
            if (!isset(\$GLOBALS["required_js"][$key])) {
                \$GLOBALS["required_js"][$key] = true;
                echo '<script src=$key type="text/javascript"></script>\n';
            }
            ?>
TEXT;
            return $result;
        });
    }
    
    /**
     * Blade directive for loading the js asset with the given key 
     * inside the css requirements array.
     * 
     * @return string Nothing to output.
     */
    private function setupRequireLocalJs() {
        Blade::directive('require_local_js',function($expression) {
            
            $key = '"'.asset($this->stringParamAsValue($expression)).'"';
            
            $result = <<<TEXT
            <?php
            if (!isset(\$GLOBALS["required_js"][$key])) {
                \$GLOBALS["required_js"][$key] = true;
                echo '<script src=$key type="text/javascript"></script>\n';
            }
            ?>
TEXT;
            
            return $result;
        });
    }
    
    /**
     * Blade directive for loading the remote css with the given key 
     * inside the css requirements array.
     * 
     * @return string Nothing to output.
     */
    private function setupRequireRemoteCss() {
        Blade::directive('require_remote_css',function($expression) {
            
            $key = $this->stringParamAsString($expression);
            
            $result = <<<TEXT
            <?php
            if (!isset(\$GLOBALS["required_css"][$key])) {
                \$GLOBALS["required_css"][$key] = true;
                echo '<link rel="stylesheet" type="text/css" href=$key />\n';
            }
            ?>
TEXT;
            return $result;
        });
    }
    
    /**
     * Bloade directive for loading the css asset with the given key 
     * inside the css requirements array.
     * 
     * @return string Nothing to output.
     */
    private function setupRequireLocalCss() {
        Blade::directive('require_local_css',function($expression) {
            
            $key = '"'.asset($this->stringParamAsValue($expression)).'"';
            $result = <<<TEXT
            <?php        
            if (!isset(\$GLOBALS["required_css"][$key])) {
                \$GLOBALS["required_css"][$key] = true;
                echo '<link rel="stylesheet" type="text/css" href=$key />\n';
            }
            ?>
TEXT;
            return $result;
        });
    }
    
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $GLOBALS["required_js"] = array();
        $GLOBALS["required_css"] = array();
        
        //requires for javascript resources
        $this->setupRequireRemoteJs();
        $this->setupRequireLocalJs();
        
        //requires for css resources
        $this->setupRequireRemoteCss();
        $this->setupRequireLocalCss();

        \Log::debug("Blade js/css require syntax setup Completed!");
        
        //next middleware
        return $next($request);
    }
    
}