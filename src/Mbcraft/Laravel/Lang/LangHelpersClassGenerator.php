<?php

namespace Mbcraft\Laravel\Lang;

use Mbcraft\Laravel\GeneratorUtils;
use Mbcraft\Piol\Dir;

/**
 * This class generates the lang helpers for the Laravel application.
 */
class LangHelpersClassGenerator {
    
    private $namespace_prefix,$language_helpers_folder_prefix;
    
    const LARAVEL_LOCALIZATION_FILES_ROOT = "/resources/lang/";
    
    function __construct() {
        $this->namespace_prefix = "App\\Lang";
        $this->language_helpers_folder_prefix = "App";
    }
        
    private function deletePreviousHelpers() {
        $d = GeneratorUtils::getGeneratedClassFolder();
        $fold = $d->newDir($this->language_helpers_folder_prefix);
        if ($fold->exists())
            $fold->delete();
    }
    
    /**
     * Rigenera tutti gli helper
     */
    function regenerate_helpers() {
        $this->deletePreviousHelpers();
        echo "Old LangHelpers deleted.\n";
        $d = GeneratorUtils::getGeneratedClassFolder();
        $app_dir = $d->newDir("App");
        $app_dir->touch();
        
        $lang_dir = $app_dir->newDir("Lang");
        $lang_dir->touch();
        
        echo "LangHelpers dir created.\n";
        $root = new LangRoot(new Dir(self::LARAVEL_LOCALIZATION_FILES_ROOT));
        echo "Lang data collected.\n";
        $merged_data = $root->getMergedData();
        echo "Locale data merged.\n";
        $merged_data->generateHelpers([],$this->namespace_prefix, $lang_dir);
        echo "LangHelpers generated.\n";
    }
    
}