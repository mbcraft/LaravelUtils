<?php

namespace Mbcraft\Laravel\Http\Middleware;

use Closure;
use Carbon\Carbon;

/**
 * Configure Carbon to use the configured application locale.
 */
class ConfigureCarbon
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        Carbon::setLocale(config('app.locale'));
        
        \Log::debug("Carbon locale setup Completed!");
        
        return $next($request);
    }
}
