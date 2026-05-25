<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'status'       => $this->status,
            'start_date'   => $this->start_date->toDateString(),
            'end_date'     => $this->end_date->toDateString(),
            'total_amount' => $this->total_amount,
            'currency'     => $this->currency,
            'notes'        => $this->notes,
            'created_at'   => $this->created_at->toDateString(),
            'workspace'    => new SpaceWorkspaceResource(
                $this->whenLoaded('workspace')
            ),
            'customer'     => new UserResource(
                $this->whenLoaded('customer')
            ),
            'review'       => new ReviewResource(
                $this->whenLoaded('review')
            ),
        ];
    }
}
