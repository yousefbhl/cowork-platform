<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SpaceWorkspaceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'space_id'      => $this->space_id,
            'type'          => [
                'id'    => $this->workspaceType?->id,
                'name'  => $this->workspaceType?->name,
                'label' => $this->workspaceType?->label,
            ],
            'capacity'      => $this->capacity,
            'price_hourly'  => $this->price_hourly,
            'price_daily'   => $this->price_daily,
            'price_monthly' => $this->price_monthly,
            'currency'      => $this->currency,
            'is_active'     => $this->is_active,
        ];
    }
}
