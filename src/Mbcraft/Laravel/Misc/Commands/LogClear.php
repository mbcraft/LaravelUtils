<?php

namespace Mbcraft\Laravel\Misc\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Bus\SelfHandling;

use Mbcraft\Piol\File;

class LogClear extends Command implements SelfHandling
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'log:clear';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clears the laravel framework logs.';
    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        
        $f = new File("/storage/logs/laravel.log");
        $f->setContent("");
        
        $this->info("Log cleared succesfully! The log is now empty.");
    }
}
