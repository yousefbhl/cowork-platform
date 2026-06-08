<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class PriceFilter extends Filter
{
    protected function handle(mixed $value): Builder
    {
        [$min, $max] = array_pad(explode(',', $value), 2, null);

        return $this->builder->whereHas('workspaces', function ($q) use ($min, $max) {
            if ($min !== null && $min !== '') $q->where('price_daily', '>=', $min);
            if ($max !== null && $max !== '') $q->where('price_daily', '<=', $max);
        });
    }
}
