<?php

namespace Mbcraft\Laravel\Http\Middleware;

class __AbstractBladeSyntax {
    
    /**
     * Returns the expression content inside an array variable.
     * 
     * @param string $expression The blade directive parameter, with parentesis.
     * @return array The defined params, inside an indexed array.
     */
    protected function paramsAsIndexedArray($expression) {
        return eval("return array$expression;");
    }
    /**
     * Returns the expression content as a variable with $ from the blade expression content.
     * 
     * @param type $expression
     * @return string a variable name, with $
     */
    protected function stringParamAsVar($expression) {
        $var_name_with_delimiters = trim(substr($expression, 1,strlen($expression)-2));
        $var_name = substr($var_name_with_delimiters, 1,strlen($var_name_with_delimiters)-2);
        return '$'.$var_name;
    }
    
    /**
     * Returns a string expression, from the blade directive expression content.
     * 
     * @param type $expression
     * @return type
     */
    protected function stringParamAsString($expression) {
        $var_name_with_delimiters = trim(substr($expression, 1,strlen($expression)-2));
        return $var_name_with_delimiters;
    }
    
    /**
     * Returns a string expression, from the blade directive expression content.
     * 
     * @param type $expression
     * @return type
     */
    protected function stringParamAsValue($expression) {
        $var_name_with_delimiters = trim(substr($expression, 1,strlen($expression)-2));
        return eval("return ".$var_name_with_delimiters.";");
    }
}