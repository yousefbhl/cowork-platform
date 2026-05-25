<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SpaceResource;
use App\Models\Space;

class AdminSpaceController extends Controller
{
    public function index()
    {
        $spaces = Space::with(['host', 'photos'])
            ->latest()
            ->paginate(30);

        return SpaceResource::collection($spaces);
    }

    public function approve(Space $space)
    {
        $space->update(['status' => 'published']);
        return new SpaceResource($space);
    }

    public function reject(Space $space)
    {
        $space->update(['status' => 'paused']);
        return new SpaceResource($space);
    }

    public function destroy(Space $space)
    {
        $space->delete();
        return response()->json(['message' => 'Space removed']);
    }
}
