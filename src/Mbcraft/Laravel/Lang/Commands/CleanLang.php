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
    protected $signature = 'lang:clean 
    {ref_lang : The name of the reference language} 
    {target_lang : The name of the language to clean} 
    {--all : If all the missing keys should be removed without asking confirmation}';

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
        return $this->argument('ref_lang');
    }

    /**
     * Get the target argument
     *
     * @return string
     */
    protected function getTargetArgument()
    {
        return $this->argument('target_lang');
    }

    /**
     * @return boolean if this option is set
     */
    protected function getAllOption() {
        return $this->option("all");
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['ref_lang' , null , InputArgument::REQUIRED, 'The name of the reference language'],
            ['target_lang' ,null, InputArgument::REQUIRED, 'The name of the language to clean'],
        ];
    }

    protected function getOptions()
    {
        return [
            ['--all', null ,InputOption::VALUE_NONE ,'If all the missing keys should be removed without asking confirmation']
        ];
    }

    private $ref_lang_dir;
    private $target_lang_dir;
    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $this->ref_lang_dir = new Dir("/resources/lang/".$this->getRefArgument());
        $this->target_lang_dir = new Dir("/resources/lang/".$this->getTargetArgument());

        $files = $this->ref_lang_dir->listFiles();

        $this->processFiles($files);

        $folders = $this->ref_lang_dir->listFolders();

        $this->processFolders($folders);

    }

    private function processFolders($folders) {
        foreach ($folders as $fold) {
            $files = $fold->listFiles();
            $this->processFiles($files);
        }
    }

    private function processFiles($files) {
        foreach ($files as $f)
            $this->processFile($f);
    }

    private function processFile(File $ref_lang_file) {
        $ref_path = $ref_lang_file->getPath();
        $diff_path = substr($ref_path,strlen($this->ref_lang_dir->getPath()));
        $target_lang_file = new File($this->target_lang_dir->getPath().$diff_path);

        $this->executeFileComparison($ref_lang_file,$target_lang_file);
    }

    private function executeFileComparison(File $ref_lang_file,File $target_lang_file) {

        if ($target_lang_file->exists()) {
            $this->compareFiles($ref_lang_file,$target_lang_file);
        } else {
            $this->warn("Skipping missing target file : ".$target_lang_file->getPath());
        }

    }

    private $target_lang_file_changed;

    private function compareProps($ref_props,$context,$target_props) {
        foreach ($target_props as $key => $value) {
            if (!isset($ref_props[$key])) {
                $to_print = is_array($value) ? var_export($value,true) : $value;
                $remove = $this->confirm('Remove unused [' . $context.$key . '=' . $to_print . ']?');
                if ($remove)  {
                    unset($target_props[$key]);
                    $this->target_lang_file_changed = true;
                }
            } else {
                if (is_array($ref_props[$key]) && is_array($value))
                    $target_props[$key] = $this->compareProps($ref_props[$key],$key." -> ".$context,$target_props[$key]);
            }
        }
        return $target_props;
    }

    private function compareFiles(File $ref_lang_file,File $target_lang_file) {
        $this->target_lang_file_changed = false;
        $this->info("Comparing : " . $ref_lang_file->getPath() . " WITH " . $target_lang_file->getPath());
        $ref_props = $ref_lang_file->includeFile();
        $target_props = $target_lang_file->includeFile();

        $target_props = $this->compareProps($ref_props,"",$target_props);

        if ($this->target_lang_file_changed) {
            $this->info("Saving changes into " . $target_lang_file->getPath() . " ...");
            $this->savePHPArrayProperties($target_props,$target_lang_file);
        } else {
            $this->info("No changes to save, next file ...");
        }
    }

    private function savePHPArrayProperties($target_props,File $target_lang_file) {
        $header = <<<END_OF_HEADER
<?php

END_OF_HEADER;
        $footer = <<<END_OF_FOOTER


END_OF_FOOTER;

        $full_content = $header."return ".var_export($target_props,true).";".$footer;

        $target_lang_file->setContent($full_content);
    }

}