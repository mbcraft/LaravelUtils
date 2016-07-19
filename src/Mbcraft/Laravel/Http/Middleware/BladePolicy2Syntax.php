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

    /**
     * Contains the code for obtaining the currently auth user.
     *
     * @var string The code for getting the currently authorized user.
     */
    protected $GET_AUTH_USER = "Sentinel::getUser()";

    /**
     * Defines the @ican directive for Blade.
     */
    private function setupIcanBladeExtension() {

        \Log::debug("Setup @ican directive for blade ...");

        Blade::directive('ican', function($expression) {
            $params = $this->stringParamAsString($expression);
            $params = explode(',',$params);
            $method = $this->stringParamAsString($params[0]);
            if (count($params)>1) {
                array_shift($params);
                $policy_subject = $params[0];
                array_unshift($params,$this->GET_AUTH_USER);
                $method_params = join(",",$params);
            } else {
                $policy_subject = $this->GET_AUTH_USER;
                $method_params = $this->GET_AUTH_USER;
            }

            return "<?php if( policy($policy_subject)->$method($method_params) ): ?>";
        });
    }

    /**
     * Defines the @icannot directive for Blade.
     */
    private function setupIcannotBladeExtension() {

        \Log::debug("Setup @icannot directive for blade ...");

        Blade::directive('icannot', function($expression) {
            $params = $this->stringParamAsString($expression);
            $params = explode(',',$params);
            $method = $this->stringParamAsString($params[0]);
            if (count($params)>1) {
                array_shift($params);
                $policy_subject = $params[0];
                array_unshift($params,$this->GET_AUTH_USER);
                $method_params = join(",",$params);
            } else {
                $policy_subject = $this->GET_AUTH_USER;
                $method_params = $this->GET_AUTH_USER;
            }

            return "<?php if( !policy($policy_subject)->$method($method_params) ): ?>";
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
        //overriding policy checking '@ican' and '@icannot' directives for blade
        $this->setupIcanBladeExtension();
        $this->setupIcannotBladeExtension();

        \Log::debug("Blade directives '@ican' and '@icannot' setup Completed!");
        //next middleware
        return $next($request);
    }
}