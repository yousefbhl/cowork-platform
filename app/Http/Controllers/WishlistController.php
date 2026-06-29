<?php

namespace App\Http\Controllers;

use App\Http\Resources\SpaceResource;
use App\Models\Space;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        $spaces = $request->user()
            ->wishlistedSpaces()
            ->with(['photos', 'workspaces.workspaceType', 'amenities'])
            ->get();

        return SpaceResource::collection($spaces);
    }

    public function store(Request $request, Space $space)
    {
        $request->user()->wishlistedSpaces()->syncWithoutDetaching([$space->id]);

        return response()->json(['saved' => true]);
    }

    public function destroy(Request $request, Space $space)
    {
        $request->user()->wishlistedSpaces()->detach($space->id);

        return response()->json(['saved' => false]);
    }
}
