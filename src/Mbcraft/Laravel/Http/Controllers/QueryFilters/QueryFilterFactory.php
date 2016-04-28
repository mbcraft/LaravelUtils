<?php

namespace Mbcraft\Laravel\Http\Controllers\QueryFilters;

class QueryFilterFactory {
    
    const EQUAL = "equal";
    /**
     * Creates and returns an equal filter for the query.
     * 
     * @param string $column The column to use for the equal filter
     * @param mixed $value The value to use in the filter.
     * @return \Mbcraft\Laravel\Http\Controllers\QueryFilters\EqualFilter The filter
     */
    static function equal($column,$value) {
        return new EqualFilter($column,$value);
    }
    
    /**
     * 
     * @param Builder $query_builder
     * @param array $filters
     * @return mixed The builder with all the filters applied
     */
    static function applyAll($query_builder,$filters) {
        foreach ($filters as $flt) {
            $query_builder = $flt->apply($query_builder);
        }
        return $query_builder;
    }
}