<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SpaceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'title'         => $this->title,
            'slug'          => $this->slug,
            'description'   => $this->description,
            'address'       => $this->address,
            'lat'           => $this->lat,
            'lng'           => $this->lng,
            'city'          => $this->city,
            'country'       => $this->country,
            'status'        => $this->status,
            'rating_avg'    => $this->rating_avg,
            'reviews_count' => $this->reviews_count,

            'cover_photo'   => $this->whenLoaded('photos', function () {
                $cover = $this->photos->firstWhere('is_cover', true)
                    ?? $this->photos->first();
                return $cover?->path;
            }),

            'photos'        => $this->whenLoaded('photos', fn() =>
                $this->photos->map(fn($p) => [
                    'id'       => $p->id,
                    'path'     => $p->path,
                    'order'    => $p->order,
                    'is_cover' => $p->is_cover,
                ])
            ),
            'amenities'     => AmenityResource::collection(
                $this->whenLoaded('amenities')
            ),
            'workspaces'    => SpaceWorkspaceResource::collection(
                $this->whenLoaded('workspaces')
            ),
            'host'          => new UserResource(
                $this->whenLoaded('host')
            ),
        ];
    }
}
