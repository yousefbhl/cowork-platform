<?php

namespace App\Http\Controllers;

use App\Http\Resources\SpaceResource;
use App\Http\Resources\ReviewResource;
use App\Models\Space;

class SpaceController extends Controller
{
    public function index()
    {
        $spaces = Space::with(['photos', 'amenities', 'workspaces.workspaceType'])
            ->published()
            ->paginate(20);

        return SpaceResource::collection($spaces);
    }

    public function show(Space $space)
    {
        $space->load(['photos', 'amenities', 'workspaces.workspaceType', 'host']);

        return new SpaceResource($space);
    }
}
