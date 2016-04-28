<?php

namespace Mbcraft\Laravel;

use Mbcraft\Piol\Dir;

class GeneratorUtils {
    
    const DEFAULT_GENERATED_CLASS_FOLDER = "/storage/generated_classes/";
    
    /**
     * Effettua l'escape del nome del metodo nel caso in cui esso sia una keyword riservata.
     * 
     * @param string $method_name Il nome del metodo
     * @return string Il metodo risultante
     */
    public static function escapeIfKeyword($method_name) {
        if (self::isKeyword($method_name))
            return $method_name."_";
        else
            return $method_name;
    }
    
    /**
     * Controlla se una stringa è esattamente una keyword riservata del linguaggio php.
     * 
     * @param string $key La stringa da controllare.
     * @return boolean true se la stringa è una parola chiave del php, false altrimenti.
     */
    public static function isKeyword($key) {
        
        static $kl = array("__halt_compiler","abstract","and","array","as",
            "break","callable","case","catch","class","clone","const","continue",
            "declare","default","die","do","echo","else","elseif","empty","enddeclare",
            "endfor","endforeach","endif","endswitch","endwhile","eval","exit","extends",
            "final","finally","for","foreach","function","global","goto","if","implements",
            "include","include_once","instanceof","insteadof","interface","isset","list",
            "namespace","new","or","print","private","protected","public","require",
            "require_once","return","static","switch","throw","trait","try","unset","use",
            "var","while","xor","yield");
        
        return array_search($key,$kl)!==FALSE;
    }
    
    /**
     * Ritorna il nome del metodo dalla chiave. Gli or ( | ) 
     * vengono sostituiti con underscore ( | ).
     * 
     * @param string $key La chiave usata per la traduzione.
     * @return string Il nome del metodo
     */
    public static function getMethodNameFromKey($key) {
        //escape dei caratteri meno con underscore
        $escaped_key = str_replace("-", "_", $key);
        $escaped_key = str_replace("/", ".", $escaped_key);
        //rimozione dei livelli di parentela
        list($levels,$prepared_key) = self::removeParentLevels($escaped_key);
        
        $method_name_parts = array();
        //divisione delle parti separate dal punto e capitalizzazione
        $parts = explode(".", $prepared_key);

        foreach ($parts as $part) {
            $i = 0;
            //ignoro tutti gli underscore iniziali
            while ($part{$i}=='_') $i++;
            $part = substr($part, $i);
            $toks = explode("_", $part);
            $part_name = $toks[0];
            for ($i = 1; $i < count($toks); $i++) {
                $tk = $toks[$i];
                //le costanti maiuscole e intere restano inviariate
                if (strtoupper($tk)==$tk) {
                    $part_name .= "_".$tk;
                } 
                else {
                    //le altre parti vengono capitalizzate
                    $capitalized_tk = strtoupper($tk{0}) . substr($tk, 1);
                    $part_name .= $capitalized_tk;
                }
            }

            $method_name_parts[] = $part_name;
        }
        //riunione delle parti
        $almost_final = join("_", $method_name_parts);
        //escape da keyword del linguaggio
        $escaped = self::escapeIfKeyword($almost_final);
        //riaggiunta dei livelli di parentela
        return self::addParentLevels($levels, $escaped);
         
    }
    
    /**
     * Ritorna la chiave a partire dal nome del metodo del LangHelper.
     * 
     * @param string $method_name Il nome del metodo
     * @return string La chiave
     */
    public static function getKeyFromMethodName($method_name) {
        $parts = explode("_", $method_name);

        $key_part_list = [];
        foreach ($parts as $part) {
            $key_part = "";
            for ($i = 0; $i < strlen($part); $i++) {
                $ch = $part{$i};
                if (strtoupper($ch) == $ch) {
                    $key_part .= "_" . strtolower($ch);
                } else
                    $key_part .= $ch;
            }
            $key_part_list[] = $key_part;
        }

        return join(".", $key_part_list);
    }
    
    /**
     * Ritorna l'elemento del namespace data la stringa.
     * 
     * @param string $st La stringa da convertire in parte di namespace.
     * @return string La parte di namespace
     */
    public static function getNamespacePartFromString($st) {
        $partial = self::getMethodNameFromKey($st);
        return strtoupper($partial{0}).substr($partial,1);
    }
    /**
     * Ritorna il namespace con l'aggiunta della chiave specificata.
     * 
     * @param string $namespace Il namespace di partenza
     * @param string $key La chiave
     * @return string Il namespace con l'aggiunta
     */
    public static function getNamespaceWith($namespace,$key) {
        return $namespace."\\".self::getNamespacePartFromString($key);
    }
    
    /**
     * Rileva i livelli di parentela di una chiave e li rimuove, restituendoli come parte 
     * del risultato.
     * 
     * @param string $key La chiave a cui rimuovere i livelli di parentela ( | )
     * @return array Il numero dei livelli e la chiave ripulita, come array
     */
    private static function removeParentLevels($key) {
        $i = 0;
        while ($key{$i}=='|') $i++;
        return array($i,substr($key,$i));
    }

    /**
     * Riaggiunge al nome del metodo i livelli di parentela anteponendo degli underscore.
     * 
     * @param integer $levels Il numero dei livelli
     * @param string $method_name Il nome del metodo a cui aggiungere i livelli di parentela
     * @return string La chiave coi livelli di parentela
     */
    private static function addParentLevels($levels,$method_name) {
        $prefix = str_pad("", $levels, "_");
        return $prefix.$method_name;
    }
    
    /**
     * Returns the folder to use for saving generated classes.
     * 
     * @return \Mbcraft\Piol\Dir The dir instance pointing to the 
     *      generated classes folder.
     */
    public static function getGeneratedClassFolder() {
        $d = new Dir(self::DEFAULT_GENERATED_CLASS_FOLDER);
        $d->touch();
        
        return $d;
    }
}