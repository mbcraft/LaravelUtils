<?php

namespace Mbcraft\Laravel\Lang;

use Mbcraft\Piol\File;
use Mbcraft\Piol\Dir;

use Mbcraft\Laravel\GeneratorUtils;

class LangFile {
    
    private $data,$name,$flattened;
    
    function __construct(File $f) {
        $this->name = $f->getName();
        
        $file_php_content = $f->getContent();
        $pure_php_content = str_replace("<?php", "", $file_php_content);
        $this->data = eval($pure_php_content);
        
        $this->flattened = LangGeneratorHelper::flattenKeys($this->data);
    }
    
    function getName() {
        return $this->name;
    }
    
    function getData() {
        return $this->data;
    }
    
    function isFolder() {
        return false;
    }
    
    function isFile() {
        return true;
    }
    
    public function dump($tabs) {
        for ($i=0;$i<$tabs;$i++)
            echo "\t";
        echo "LangFile [".$this->name."] : ".count($this->data)." elements.\n";
    }
    
    function mergeWith(LangFile $file) {
        $this->flattened = array_merge($this->flattened, $file->flattened);
    }
    
    /**
     * Ritorna l'elenco completo delle chiavi comprensivo delle classi da cui eredita quella che si sta generando (ereditarietà virtuale).
     * 
     * @param array $inheritance_list La lista di LangFolders parent di questo LangFile.
     */
    private function getFullKeySet($previous_folder_list) {
        $final_keys = $this->flattened;
        for ($i=count($previous_folder_list)-2;$i>=0;$i--) {
            $lf = $previous_folder_list[$i];
            
            if ($lf->hasLangFile($this->getName())) {
                $parent_lang = $lf->getLangFile($this->getName());
                $final_keys = LangGeneratorHelper::importKeys($final_keys, $parent_lang->flattened, count($previous_folder_list)-$i-1);
            }
        }
        return $final_keys;
    }
    
    /**
     * Ritorna la lista dei prefissi dalla lista delle cartelle precedenti.
     * 
     * @param array $previous_folder_list La lista delle cartelle precedenti
     * @return array La lista dei prefissi
     */
    public function getPrefixList($previous_folder_list) {
        $prefix_list = [];
        foreach ($previous_folder_list as $fold) {
            $prefix_list[] = $fold->getName();
        }
        $prefix_list[] = $this->getName();
        return $prefix_list;
    }
    
    /**
     * Ritorna il nome della classe helper per questo LangFile.
     * 
     * @return string Il nome della classe Helper
     */
    private function getHelperClassName() {
        $as_method_name = GeneratorUtils::getMethodNameFromKey($this->name);
        return "L".strtoupper($as_method_name{0}).substr($as_method_name, 1);
    }
    
    /**
     * Genera la classe Helper per questo file di localizzazione.
     * 
     * @param array $previous_folder_list La lista delle cartelle precedenti (LangFolder)
     * @param string $namespace Il namespace in cui generare l'helper.
     * @param \Mbcraft\Piol\Dir $dir La cartella in cui salvare l'helper.
     */
    public function generateHelpers($previous_folder_list,$namespace,Dir $dir) {
        //get the final class name
        $class_name = $this->getHelperClassName();
        //get the full keys set with levels and overlaps
        $all_data = $this->getFullKeySet($previous_folder_list);
        //echo "Getting prefix list ...\n";
        $previous_folder_keys = $this->getPrefixList($previous_folder_list);
        //echo "Generating helper class ...\n";
        LangGeneratorHelper::createHelperClass($dir,$namespace,$class_name,$previous_folder_keys,$all_data);
        //echo "Helper class generated!\n";
        
    }
    
    /**
     * Deep cloning
     */
    function __clone() {
        foreach ($this->flattened as $k => $v) {
            $this->flattened[$k] = clone $v;
        }
    }
    
}