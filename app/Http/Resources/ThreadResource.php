<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ThreadResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'last_message_at' => $this->last_message_at,
            'space'           => new SpaceResource($this->whenLoaded('space')),
            'messages'        => MessageResource::collection(
                $this->whenLoaded('messages')
            ),
        ];
    }
}
