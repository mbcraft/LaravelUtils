<?php
/**
 * Created by PhpStorm.
 * User: marco
 * Date: 16/07/16
 * Time: 18.36
 */

namespace Mbcraft\Laravel\Lang\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Bus\SelfHandling;
use Mbcraft\Piol\Dir;

class OverviewLang extends Command implements SelfHandling
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lang:overview';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Shows the currently available and hidden languages.';


    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $available_lang_dir = new Dir("/resources/lang/");
        if ($available_lang_dir->exists()) {
            $this->info("Available languages :");
            $this->info("");
            $folds = $available_lang_dir->listFolders();
            foreach ($folds as $f) {
                $this->info("[" . $f->getName() . "]");
            }
        } else {
            $this->warn("Localization directory ".$available_lang_dir->getPath()." does not exists!!");
        }

        $hidden_lang_dir = new Dir("/storage/app/hidden_lang/");
        $this->info("");
        if ($hidden_lang_dir->exists()) {
            $this->info("Hidden languages :");
            $this->info("");
            $folds = $hidden_lang_dir->listFolders();
            foreach ($folds as $f) {
                $this->info("[" . $f->getName() . "]");
            }
        } else {
            $this->info("No hidden localizations found.");

        }




    }

}