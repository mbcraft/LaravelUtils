<?php

namespace Mbcraft\Laravel\Lang;

/**
 * Questa classe rappresenta il valore di una chiave e il suo livello, dato dalla parentela
 * rispetto al file su cui viene calcolata.
 */
class KeyValue {
        
    public $value;
    public $level=1;
    /**
     * Il livello è usato per determinare quanti prefissi utilizzare per
     * determinare la chiave esatta della traduzione da cercare
     * @param string $val Il valore della chiave. Non è importante e non viene utilizzato in quanto più dipendere da quale lingua viene elaborata per prima.
     * @param integer $level Il livello rispetto alla foglia di questa chiave nell'intera gerarchia di cartelle dei file di lingua (più è alto più è vicino alla radice).
     */
    function __construct($val,$level = 1) {
        $this->value = $val;
        $this->level = $level;
    }
    
}