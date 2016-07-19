<?php

namespace Mbcraft\Laravel\Http\Middleware;

use Closure;
use Blade;

/**
 * This middleware adds blade support for :
 *
 * @can -> redefined to work with policy helper function
 *
 */
class BladePolicy2Syntax extends __AbstractBladeSyntax {

    private function setupWorkingCanBladeExtension() {

        \Log::debug("Setup ican directive for blade ...");

        Blade::directive('ican', function($expression) {

            $params = $this->paramsAsIndexedArray($expression);
            $method = array_shift($params);
            $policy_subject = count($params) > 0 ? $params[0] : "Sentinel::getUser()";
            array_unshift($params,"Sentinel::getUser()");

            $method_params = join(',',$params);
            return "<?php \n"
            . "\t if (policy($policy_subject)->$method($method_params)): \n"
            . " ?>\n";
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
        //adds working @can support
        $this->setupWorkingCanBladeExtension();

        \Log::debug("Blade policy (@can) override setup Completed!");
        //next middleware
        return $next($request);
    }
}