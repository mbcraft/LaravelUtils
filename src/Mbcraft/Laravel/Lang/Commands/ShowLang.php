<?php

namespace Mbcraft\Laravel\Lang\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Bus\SelfHandling;
use Mbcraft\Piol\Dir;

class ShowLang extends Command implements SelfHandling
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lang:show 
    {name : The name of the language to show}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enables a previously hidden localization.';

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
            ['name', InputArgument::REQUIRED, 'The name of the language to show'],
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
        $target = new Dir("/storage/app/hidden_langs/".$lang);
        if ($target->exists()) {
            $show_dir = new Dir("/resources/lang/");
            $target->moveTo($show_dir);
            $this->info("Language ".$lang." reenabled succesfully.");
            
            $check_dir_delete_if_empty = new Dir("/storage/app/hidden_langs");
            if ($check_dir_delete_if_empty->isEmpty()) {
                $check_dir_delete_if_empty->delete();
                $this->info("Hidden languages storage dir deleted (empty).");
            }
            
        } else
            $this->error("Language ".$lang." not found.");
          
    }
}
