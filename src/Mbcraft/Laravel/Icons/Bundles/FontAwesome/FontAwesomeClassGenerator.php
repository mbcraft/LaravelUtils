<?php

namespace Mbcraft\Laravel\Icons\Bundles\FontAwesome;

use Mbcraft\Laravel\GeneratorUtils;

class FontAwesomeClassGenerator {
          
    private $sets,$namespace,$class_name;
    
    function __construct() {
        $this->sets = ["Brand","Chart","Currency","Directional","FileType","FormControls","Gender","Hand","Medical","Payment","Spinner","TextEditor","Transportation","VideoPlayer","WebApplication"];
        $this->class_name = "FA";
        $this->namespace = "";
        
    }
    
    private static function normalizedSetName($set) {
        return strtolower(substr($set,0,1)).substr($set,1);
    }
    
    private static function normalizedIconName($icon_name) {
        return strtoupper(substr($icon_name,0,1)).str_replace("-", "_", substr($icon_name,1));
    }
    
    private function getFontAwesomeIconMethod($set,$icon_prefix,$icon_name) {
        $set_method_prefix = self::normalizedSetName($set);
        $icon_name_suffix = self::normalizedIconName($icon_name);
        $result = <<<TEXT
                
    public static function {$set_method_prefix}_{$icon_name_suffix}(\$additional_classes = null,\$tooltip = null) {
        \$more_classes = self::__getAdditionalClasses(\$additional_classes);
        \$tooltip_text = \$tooltip != null ? 'title="'.\$tooltip.'"' : '';
    
        return "<i class='{$icon_prefix}{$icon_name} ".\$more_classes."' \$tooltip_text></i>";
    }
        
TEXT;
        return $result;
    }
    
    private function getFontAwesomeIconMethods() {
        $result = "";
        foreach ($this->sets as $set) {
            include_once($set.".php");
            $icon_prefix = eval("return Mbcraft\\Laravel\\Icons\\Bundles\\FontAwesome\\".$set."::ICON_PREFIX;");
            $icon_list_text = eval("return Mbcraft\\Laravel\\Icons\\Bundles\\FontAwesome\\".$set."::ICON_LIST;");
        
            $icon_list_lines = explode("\n", $icon_list_text);
            foreach ($icon_list_lines as $line) {
                $icon_name = trim(str_replace("(alias)","",$line));
                $result.=$this->getFontAwesomeIconMethod($set, $icon_prefix, $icon_name);
            }
            
        }
        return $result;
    }
    
    private function getFontAwesomeClassOpening() {
        $namespace_string = $this->namespace == "" ? "" : "namespace ".$this->namespace.";";
        $class_name = $this->class_name;
        
        $result = <<<TEXT
<?php
$namespace_string
                                
class $class_name {
                 
   private static function __getAdditionalClasses(\$additional_classes) {
        if (\$additional_classes!=null && is_array(\$additional_classes))
            return join(' ',\$additional_classes);
        else
            \$more_classes = \$additional_classes;
        if (\$more_classes==null)
            return "fa-fw";
        return \$more_classes;
   }
TEXT;
        return $result;
    }
    
    private function getFontAwesomeClassClosing() {
        $result = <<<TEXT
               
}
                
TEXT;
        return $result;
    }
    
    public function regenerate_helper() {
        $font_awesome_class_content = $this->getFontAwesomeClassOpening().$this->getFontAwesomeIconMethods().$this->getFontAwesomeClassClosing();
    
        $d = GeneratorUtils::getGeneratedClassFolder();
        $f = $d->newFile($this->class_name.".php");
        $f->setContent($font_awesome_class_content);
    }
    
}

