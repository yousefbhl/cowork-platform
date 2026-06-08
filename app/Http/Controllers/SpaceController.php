<?php

namespace App\Http\Controllers;

use App\Filters\SpaceFilter;
use App\Http\Resources\SpaceResource;
use App\Models\Space;
use Illuminate\Http\Request;

class SpaceController extends Controller
{
    public function index(Request $request, SpaceFilter $filter)
    {
        $spaces = $filter->apply(
            Space::with(['photos', 'amenities', 'workspaces.workspaceType'])
                ->published()
        )
        ->orderByDesc('rating_avg')
        ->paginate(20)
        ->withQueryString();

        return SpaceResource::collection($spaces);
    }

    public function show(Space $space)
    {
        $space->load([
            'photos',
            'amenities',
            'workspaces.workspaceType',
            'host.hostProfile',
        ]);

        return new SpaceResource($space);
    }
}
