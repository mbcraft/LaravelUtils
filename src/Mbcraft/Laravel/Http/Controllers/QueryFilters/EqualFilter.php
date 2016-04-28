<?php

namespace Mbcraft\Laravel\Http\Controllers\QueryFilters;

use Illuminate\Database\Eloquent\Builder;

class EqualFilter implements IQueryFilter {
    
    private $column,$value;
    
    function __construct($column,$value) {
        $this->column = $column;
        $this->value = $value;
    }
    
    public function getKey() {
        return $this->column;
    }
    
    public function getValue() {
        return $this->value;
    }
    
    public function apply(Builder $query_builder) {
        return $query_builder->where($this->column,$this->value);
    }

}