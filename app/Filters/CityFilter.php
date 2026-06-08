<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class CityFilter extends Filter
{
    protected function handle(mixed $value): Builder
    {
        return $this->builder->where('city', 'like', "%{$value}%");
    }
}
