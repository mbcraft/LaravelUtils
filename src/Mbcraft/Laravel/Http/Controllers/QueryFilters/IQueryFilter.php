<?php

namespace Mbcraft\Laravel\Http\Controllers\QueryFilters;

use Illuminate\Database\Eloquent\Builder;

interface IQueryFilter {
    
    /**
     * Applies the filter to the query.
     */
    function apply(Builder $query_builder);
    
    /**
     * Get the key that identifies this filter
     */
    function getKey();
    
    /**
     * Gets the value that is used for this filter
     */
    function getValue();
    
}