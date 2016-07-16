<?php

namespace Mbcraft\Laravel\Lang\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Bus\SelfHandling;
use Mbcraft\Piol\Dir;

class HideLang extends Command implements SelfHandling
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lang:hide 
    {name : The name of the language to hide}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Disables an available localization.';

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getNameArgument()
    {
        return $this->argument('name');
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the language to hide'],
        ];
    }
    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $lang = $this->getNameArgument();
        //
        $target = new Dir("/resources/lang/".$lang);
        if ($target->exists()) {
            
            $hide_dir = new Dir("/storage/app/hidden_langs");
            if (!$hide_dir->exists()) {
                $this->info("Hidden languages storage dir created.");
                $hide_dir->touch();
            }
            $target->moveTo($hide_dir);
            $this->info("Language ".$lang." hidden succesfully.");
        } else
            $this->error("Language ".$lang." not found.");
          
    }
}
