<?php

namespace App\Models\Traits;

use App\Http\Filter\AbstractFilter;
use Illuminate\Contracts\Database\Eloquent\Builder;

trait HasFilter
{
    public function scopeFilter(Builder $builder, AbstractFilter $filter)
    {
        $filter->applyFilter($builder);
        return $builder;
    }
}