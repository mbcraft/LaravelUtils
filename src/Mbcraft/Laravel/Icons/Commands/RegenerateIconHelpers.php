<?php

namespace Mbcraft\Laravel\Icons\Commands;

use Mbcraft\Laravel\FontAwesome\FontAwesomeClassGenerator;

use Illuminate\Console\Command;
use Illuminate\Contracts\Bus\SelfHandling;

/**
 * This command is used to regenerate the FontAwesome helper class.
 * After registering it inside the Kernel class, you can call it using :
 * 
 * ./artisan icons:regenerate_helper
 */
class RegenerateIconHelpers extends Command implements SelfHandling
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'icons:regenerate_helpers';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Regenerates the icons helpers.';

    
    protected $bundles = ["FontAwesome"];
    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        foreach ($this->bundles as $bundleName) {
            $className = "Mbcraft\\Laravel\\Icons\\Bundles\\".$bundleName."\\".$bundleName."ClassGenerator";
        
            $generator = new $className();
            $generator->regenerate_helper();
            $this->info("Icon helper for bundle '".$bundleName."' generated.");
        }
            
        $this->info("Icon helpers generated succesfully.");
        
    }
}
