<?php

namespace Mbcraft\Laravel\Http\Middleware;

use Closure;

/**
 * This middleware defines aliases for the localization functions
 * trans and trans_choice, lang and lang_choice respectively.
 */
class LocalizationAliases {

    function handle($request, Closure $next) {

        if (function_exists('lang') || function_exists('lang_choice'))
            throw new \Exception("functions lang or lang_choice are already defined.");
        
        $code = <<<TEXT

        function lang(\$id, \$parameters = array(), \$domain = 'messages', \$locale = null) {
            return trans(\$id, \$parameters, \$domain, \$locale);
        }

        function lang_choice(\$id, \$number, \$parameters = array(), \$domain = 'messages', \$locale = null) {
            return trans_choice(\$id, \$number, \$parameters, \$domain, \$locale);
        }

TEXT;
        
        eval($code);
        
        if (!function_exists('lang') || !function_exists('lang_choice'))
            throw new \Exception("functions lang or lang_choice have not been defined.");
           
        \Log::debug("Localization alias functions definition (lang,lang_choice) initialized!");
        
        return $next($request);
    }

}
