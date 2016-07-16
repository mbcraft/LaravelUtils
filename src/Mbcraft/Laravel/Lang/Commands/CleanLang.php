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
use Symfony\Component\Console\Input\InputArgument;

class CleanLang extends Command implements SelfHandling
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lang:clean {ref} {target}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleans unused localization keys comparing from a reference localization.';

    /**
     * Get the ref argument
     *
     * @return string
     */
    protected function getRefArgument()
    {
        return $this->argument('ref');
    }

    /**
     * Get the target argument
     *
     * @return string
     */
    protected function getTargetArgument()
    {
        return $this->argument('target');
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['ref', InputArgument::REQUIRED, 'The name of the reference language.'],
            ['target', InputArgument::REQUIRED, 'The name of the language to clean']
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