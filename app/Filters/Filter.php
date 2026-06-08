<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

abstract class Filter
{
    protected Builder $builder;

    public function apply(Builder $builder, mixed $value): Builder
    {
        $this->builder = $builder;
        return $this->handle($value);
    }

    abstract protected function handle(mixed $value): Builder;
}
