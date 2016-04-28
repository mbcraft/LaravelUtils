<?php

namespace Mbcraft\Laravel\Http\Middleware;

use Closure;
use Blade;
use View;

/**
 * Adds support to the blade templating engine for :
 * 
 * @widget <-- insert the widget named '<widget_group>/<widget_name>' inside the document.
 */
class BladeWidgetsSyntax extends __AbstractBladeSyntax {
    
    private function getCommonWidgetPhpIncludeCode($widget_group) {
        return "\t if (!isset(\$GLOBALS['widgets'])) { \$GLOBALS['widgets'] = array('widget_stack' => array(),'requires' => array()); }\n"
            . "\t if (!isset(\$GLOBALS['widgets']['requires']['$widget_group'])) {\n"
            . "\t \$GLOBALS['widgets']['requires']['$widget_group'] = true; \n"
            . "\t echo \$__env->make('widgets/$widget_group/setup', array_except(get_defined_vars(), array('__data', '__path')))->render();"
            . "\t }\n";
    }
    
    private function setupBeginWidget() {
        Blade::directive('begin_widget', function($expression) {
            $params = $this->paramsAsIndexedArray($expression);
            
            $full_widget_name = $params[0];
            list($widget_group,$widget) = explode("/",$full_widget_name);
            
            if (!View::exists("widgets/$widget_group/$widget"."__begin")) throw new \Exception("Widget ".$full_widget_name." begin part not found.");
            if (!View::exists("widgets/$widget_group/$widget"."__end")) throw new \Exception("Widget ".$full_widget_name." end part not found.");
            
            if (count($params)>1) {
                $exported_params = var_export($params[1],true);
            } else {
                $exported_params = "array()";
            }
            return "<?php \n"
                . $this->getCommonWidgetPhpIncludeCode($widget_group)
                . "\t \$GLOBALS['widgets']['widget_stack'][] = array('full_widget_name' => '$full_widget_name', 'params' => $exported_params); \n"
                . "\t echo \$__env->make('widgets/$widget_group/".$widget."__begin', array_merge($exported_params,array_except(get_defined_vars(), array('__data', '__path'))))->render();\n"
                . "\t ?>\n";
        });
    }
    
    private function setupEndWidget() {
        Blade::directive('end_widget', function($expression) {
            
            return "<?php \n"
                . "\t extract(array_pop(\$GLOBALS['widgets']['widget_stack']),EXTR_OVERWRITE); \n"
                . "\t list(\$widget_group,\$widget) = explode('/',\$full_widget_name); \n"
                . "\t echo \$__env->make('widgets/'.\$widget_group.'/'.\$widget.'__end', array_merge(\$params,array_except(get_defined_vars(), array('__data', '__path'))))->render();\n"
                . "\t ?>\n";
        });
    }
    
    /**
     * Defines the @widget helper for using widgets inside code.
     * Each widgets has a mandatory one-time setup inside 'widgets/<widget_group>/setup'
     * that is loaded once. The code is inside 'widgets/<full_widget_name>', where
     * <full_widget_name> is '<widget_group>/<widget>'.
     */
    private function setupWidget() {
        Blade::directive('widget', function($expression) {
            $params = $this->paramsAsIndexedArray($expression);
            $full_widget_name = $params[0];
            
            list($widget_group,$widget) = explode("/",$full_widget_name);
            if (count($params)>1) {
                $exported_params = var_export($params[1],true);
            } else {
                $exported_params = "array()";
            }
                    
            if (View::exists("widgets/$widget_group/$widget")) {
                return "<?php \n"
                    . $this->getCommonWidgetPhpIncludeCode($widget_group)
                    . "\t echo \$__env->make('widgets/$widget_group/$widget', array_merge($exported_params,array_except(get_defined_vars(), array('__data', '__path'))))->render();\n"
                    . "\t ?>\n";
            } else {
                if (View::exists("widgets/$widget_group/$widget"."__begin") && View::exists("widgets/$widget_group/$widget"."__end")) {
                    return "<?php \n"
                    . $this->getCommonWidgetPhpIncludeCode($widget_group)
                    . "\t echo \$__env->make('widgets/$widget_group/".$widget."__begin', array_merge($exported_params,array_except(get_defined_vars(), array('__data', '__path'))))->render();\n"
                    . "\t echo \$__env->make('widgets/$widget_group/".$widget."__end', array_merge($exported_params,array_except(get_defined_vars(), array('__data', '__path'))))->render();\n"
                    . "\t ?>\n";
                } else
                    throw new \Exception("Widget not found : ".$full_widget_name);
            }
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
        //adds @begin_widget and @end_widget
        $this->setupBeginWidget();
        $this->setupEndWidget();
        //adds @widget support to blade templating engine.
        $this->setupWidget();

        \Log::debug("Blade widgets syntax (@widget) setup Completed!");
        //next middleware
        return $next($request);
    }
}