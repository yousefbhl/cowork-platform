<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class SpaceFilter
{
    protected array $filters = [
        'city'      => CityFilter::class,
        'type'      => WorkspaceTypeFilter::class,
        'amenities' => AmenitiesFilter::class,
        'price'     => PriceFilter::class,
    ];

    public function __construct(protected Request $request) {}

    public function apply(Builder $builder): Builder
    {
        foreach ($this->filters as $param => $filterClass) {
            if ($this->request->filled($param)) {
                (new $filterClass)->apply($builder, $this->request->get($param));
            }
        }

        return $builder;
    }
}
