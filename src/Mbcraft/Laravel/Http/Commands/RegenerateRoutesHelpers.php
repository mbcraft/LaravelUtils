<?php

namespace Mbcraft\Laravel\Http\Commands;

use Illuminate\Console\Command;
use Mbcraft\Laravel\Http\RoutesClassGenerator;
use Mbcraft\Laravel\Http\FormButtonJSRoutesClassGenerator;
use Illuminate\Contracts\Bus\SelfHandling;

use Mbcraft\Piol\File;
/**
 * This class regenerates the Routes helper with all the available cached routes.
 * Before regenerating the helper it is necessary to cache the routes using
 * the artisan command :
 * 
 * artisan route:cache
 */
class RegenerateRoutesHelpers extends Command implements SelfHandling
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'route:regenerate_helpers';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Regenerates the routes helpers.';

    private function hasRoutesCache() {
        $routes_cache = new File("/bootstrap/cache/routes.php");
        if (!$routes_cache->exists()) {
            return false;
        }
        
        return true;
    }
    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        
        if ($this->hasRoutesCache()) {
            
            $routes_generator = new RoutesClassGenerator();
            $routes_generator->regenerate_helper();
            
            $this->info("Routes helper generated succesfully.");
            
            $js_routes_generator = new FormButtonJSRoutesClassGenerator();
            $js_routes_generator->regenerate_helper();
            
            $this->info("FormButtonJSRoutes helper generated succesfully.");
            
        } else {
            $this->error("Route cache not found. Route cache is needed for helper generation.");
            return;
        }
    }
}
