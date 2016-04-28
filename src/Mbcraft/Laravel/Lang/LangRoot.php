<?php

namespace Mbcraft\Laravel\Lang;

use Mbcraft\Piol\Dir;

use Mbcraft\Laravel\Lang\AvailableLocale;

class LangRoot {
    
    private $locales = array();
    
    function __construct(Dir $d) {
        
        $folders = $d->listFolders();
        
        foreach ($folders as $fld) {
            $lang_folder = new AvailableLocale($fld);
            $this->locales[$lang_folder->getName()] = $lang_folder;
        }
    }
    
    /**
     * Ritorna il numero di lingue rilevate.
     * 
     * @return integer Il numero delle lingue supportate
     */
    public function availableLocales() {
        return array_keys($this->locales);
    }
    
    /**
     * Effettua il dump dei dati su standard output.
     * 
     * @param integer $tabs Numero dei tab di indentazione di partenza.
     */
    public function dump($tabs=0) {
        for ($i=0;$i<$tabs;$i++)
            echo "\t";
        echo "The LangRoot contains ".count($this->locales)." locales.\n";
        foreach ($this->locales as $k => $loc) {
            echo "Locale [".$loc->getLocale()."] :";
            $loc->dump($tabs+1);
        }
    }
    
    /**
     * Ritorna il merge di tutti i locale disponibili.
     * 
     * @return array Un elenco di LangFolder e LangFile con tutti i dati sovrapposti.
     */
    public function getMergedData() {
        $cloned = null;
        foreach ($this->locales as $k => $loc) {
            if ($cloned == null)
                $cloned = clone $loc;
            else
                $cloned->mergeWith($loc);
        }
        
        return $cloned;
    }
    
}