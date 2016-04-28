<?php

namespace Mbcraft\Laravel\Lang\Commands;

use Mbcraft\Laravel\Lang\LangHelpersClassGenerator;

use Illuminate\Console\Command;
use Illuminate\Contracts\Bus\SelfHandling;

/**
 * 
 * Rigenera tutte le classi helper da utilizzare per le traduzioni.
 */
class RegenerateLangHelpers extends Command implements SelfHandling
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lang:regenerate_helpers';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Regenerates the lang helpers for faster translation.';

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $gen = new LangHelpersClassGenerator();
        $gen->regenerate_helpers();
        
        $this->info("Lang helpers regenerated succesfully.");
    }
}
