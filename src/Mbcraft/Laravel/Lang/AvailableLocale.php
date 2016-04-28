<?php

namespace Mbcraft\Laravel\Lang;

use Mbcraft\Laravel\Lang\LangFolder;

/**
 * Questa classe rappresenta un locale disponibile.
 * Sarebbe da aggiungere qualche controllo sul nome della cartella
 * (es: che sia "it", o che sia in un elenco di locale supportati.)
 */
class AvailableLocale extends LangFolder {
    
    /**
     * Ritorna la stringa che identifica il locale che ha radice in questa cartella.
     * 
     * @return string il locale come stringa
     */
    public function getLocale() {
        return $this->name;
    }
    
    public function dump($tabs) {
        for ($i=0;$i<$tabs;$i++)
            echo "\t";
        echo "AvailableLanguage [".$this->name."] : ".count($this->getContent())." elements.\n";
        foreach ($this->getContent() as $cnt) {
            $cnt->dump($tabs+2);
        }
    }
}