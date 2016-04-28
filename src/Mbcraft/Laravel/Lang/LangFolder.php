<?php

namespace Mbcraft\Laravel\Lang;

use Mbcraft\Piol\Dir;
use Mbcraft\Laravel\Lang\LangFile;

use Mbcraft\Laravel\GeneratorUtils;

class LangFolder {
    
    protected $folders = array();
    protected $files = array();
    protected $name;
    
    public function __construct(Dir $d) {
        
        $this->name = $d->getFullName();
        
        $folders = $d->listFolders();
                
        foreach ($folders as $fld) {
            $this->folders[$fld->getName()] = new LangFolder($fld);
        }
        
        $files = $d->listFiles();
        
        foreach ($files as $fl) {
            $this->files[$fl->getName()] = new LangFile($fl);
        }
    }
    
    function getName() {
        return $this->name;
    }
    
    function getContent() {
        return array_merge($this->folders,$this->files);
    }
    
    function getFolders() {
        return $this->folders;
    }
    
    function getFiles() {
        return $this->files;
    }
    
    function isFolder() {
        return true;
    }
    
    function isFile() {
        return false;
    }
    
    public function dump($tabs) {
        for ($i=0;$i<$tabs;$i++)
            echo "\t";
        echo "LangFolder [".$this->name."] : ".count($this->getContent())." elements.\n";
        foreach ($this->getContent() as $cnt) {
            $cnt->dump($tabs+1);
        }
    }
    
    /**
     * Controlla se il LangFile con la chiave specificata esiste.
     * 
     * @param string $key La chiave del LangFile.
     * @return boolean true se il LangFile cercato esiste, false altrimenti.
     */
    public function hasLangFile($key) {
        return isset($this->files[$key]);
    }
    
    /**
     * Ritorna il LangFile con la chiave specificata.
     * 
     * @param string $key La chiave del LangFile
     * @return LangFile il LangFile cercato.
     */
    public function getLangFile($key) {
        return $this->files[$key];
    }
    
    /**
     * Effettua il merge con un'altra cartella corrispondente (dello stesso nome e livello).
     * 
     * @param \Mbcraft\Laravel\Lang\LangFolder $fold
     */
    function mergeWith(LangFolder $fold) {
        //folders
        foreach ($fold->folders as $k => $elem) {
            if (isset($this->folders[$k])) {
                $this->folders[$k]->mergeWith($fold->folders[$k]);
            } else {
                $this->folders[$k] = clone $elem;
            }
        }
        //files
        foreach ($fold->files as $k => $elem) {
            if (isset($this->files[$k])) {
                $this->files[$k]->mergeWith($fold->files[$k]);
            } else {
                $this->files[$k] = clone $elem;
            }
        }
    }
    
    /**
     * Genera le classi helper.
     * 
     * @param array $previous_folder_list L'array dei predecessori dell'elemento corrente da considerare
     * @param string $namespace Il namespace corrente
     * @param \Mbcraft\Piol\Dir $dir La directory corrente
     */
    function generateHelpers($previous_folder_list,$namespace,Dir $dir) {

        $previous_folder_list[] = $this;
        
        foreach ($this->folders as $k => $cnt) {
            $namespace_dir = GeneratorUtils::getNamespacePartFromString($k);
            $sub_dir = $dir->newDir($namespace_dir);
            $sub_dir->touch();
            $cnt->generateHelpers($previous_folder_list,GeneratorUtils::getNamespaceWith($namespace,$k),$sub_dir);
        }
        foreach ($this->files as $k => $cnt)
        {
            $cnt->generateHelpers($previous_folder_list,$namespace,$dir);
        }
    }

    /**
     * Deep cloning.
     */
    function __clone() {
        foreach ($this->folders as $k => $v) {
            $this->folders[$k] = clone $v;
        }
        foreach ($this->files as $k => $v) {
            $this->files[$k] = clone $v;
        }
    }
    
}