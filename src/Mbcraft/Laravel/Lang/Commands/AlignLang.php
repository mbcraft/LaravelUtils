<?php
/**
 * Created by PhpStorm.
 * User: marco
 * Date: 16/07/16
 * Time: 11.48
 */

namespace Mbcraft\Laravel\Lang\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Bus\SelfHandling;
use Mbcraft\Piol\Dir;
use Mbcraft\Piol\File;

class AlignLang extends Command implements SelfHandling
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lang:align {ref} {target}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Align a localization adding missing keys and values using a reference localication.';

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['ref', InputArgument::REQUIRED, 'The name of the reference language.'],
            ['target', InputArgument::REQUIRED, 'The name of the language to align']
        ];
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        throw new \Exception("Not implemented yet.");
    }
}