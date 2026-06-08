<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class WorkspaceTypeFilter extends Filter
{
    protected function handle(mixed $value): Builder
    {
        return $this->builder->whereHas('workspaces.workspaceType', function ($q) use ($value) {
            $q->where('name', $value);
        });
    }
}
