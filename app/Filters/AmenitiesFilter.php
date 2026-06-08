<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class AmenitiesFilter extends Filter
{
    protected function handle(mixed $value): Builder
    {
        $ids = is_array($value) ? $value : explode(',', $value);

        foreach ($ids as $amenityId) {
            $this->builder->whereHas('amenities', function ($q) use ($amenityId) {
                $q->where('amenities.id', $amenityId);
            });
        }

        return $this->builder;
    }
}
