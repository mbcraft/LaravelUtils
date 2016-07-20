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

    //escape html entities : default to true
    private $escape_html_entities = true;

    //tag codes used in string input, you can add more codes in this array
    private $search_replace_tag_codes = array(
        '|\n|' => '<br />',
        '|B|' => '<b>',
        '|/B|' => '</b>'
    );
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lang:align 
    {ref_lang : The name of the reference language.} 
    {target_lang : The name of the language to align.}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Align a localization adding missing files, keys and values using a reference localization.';

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
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['ref_lang', null, InputArgument::REQUIRED, 'The name of the reference language.'],
            ['target_lang', null, InputArgument::REQUIRED, 'The name of the language to align.']
        ];
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $this->info("Settings : ");
        $this->info("Escape HTML Entities : ".($this->escape_html_entities ? "true" : "false"));
        if (!empty($this->search_replace_tag_codes)) {
            $this->info("Available tag codes for input : ");
            foreach ($this->search_replace_tag_codes as $search => $replace) {
                $this->info("Code  ".$search."  IS REPLACED WITH  ".$replace);
            }
        }
        $this->info("---------------------------------------");

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

        if (!$target_lang_file->exists()) {
            $this->info($target_lang_file->getPath()." does not exist, creating ...");
            $target_lang_file->setContent("<?php \n\n return array(); \n\n");
        }

        $this->compareFiles($ref_lang_file,$target_lang_file);

    }

    private $target_lang_file_changed;

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
            $this->info("No key is missing, next file ...");
        }
    }

    private function compareProps($ref_props,$context,$target_props) {
        foreach ($ref_props as $key => $value) {
            if (!isset($target_props[$key]) || (is_array($value)!=is_array($target_props[$key]))) {
                if (is_array($value)) {
                    $target_props[$key] = $this->compareProps($ref_props[$key], $context . " -> ".$key, array());
                } else {
                    $real_context = substr($context." -> ".$key,4);
                    $escaped_value = html_entity_decode($value);
                    $result = $this->ask("Translating key: [".$real_context."=".$escaped_value."]");
                    $target_props[$key] = $this->replaceEntitiesAndCustomTagCodes($result);
                }
                $this->target_lang_file_changed = true;
            } else {
                if (is_array($value) && is_array($target_props[$key]))
                    $target_props[$key] = $this->compareProps($ref_props[$key],$context." -> ",$target_props[$key]);
            }
        }
        return $target_props;
    }


    private function replaceEntitiesAndCustomTagCodes($input) {
        if ($this->escape_html_entities)
            $output = htmlentities($input);
        else
            $output = $input;

        foreach ($this->search_replace_tag_codes as $search => $replace) {
            $output = str_replace($search,$replace,$output);
        }

        return $output;
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