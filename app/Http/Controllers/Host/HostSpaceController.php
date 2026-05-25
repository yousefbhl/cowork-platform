<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSpaceRequest;
use App\Http\Resources\SpaceResource;
use App\Models\Space;
use Illuminate\Http\Request;

class HostSpaceController extends Controller
{
    public function index(Request $request)
    {
        $spaces = $request->user()
            ->spaces()
            ->with(['photos', 'workspaces.workspaceType', 'amenities'])
            ->latest()
            ->paginate(20);

        return SpaceResource::collection($spaces);
    }

    public function store(StoreSpaceRequest $request)
    {
        $space = $request->user()->spaces()->create($request->validated());

        if ($request->amenities) {
            $space->amenities()->sync($request->amenities);
        }

        $space->load(['photos', 'workspaces', 'amenities']);

        return new SpaceResource($space);
    }

    public function show(Request $request, Space $space)
    {
        abort_if($space->host_id !== $request->user()->id, 403);
        $space->load(['photos', 'workspaces.workspaceType', 'amenities']);
        return new SpaceResource($space);
    }

    public function update(StoreSpaceRequest $request, Space $space)
    {
        abort_if($space->host_id !== $request->user()->id, 403);

        $space->update($request->validated());

        if ($request->has('amenities')) {
            $space->amenities()->sync($request->amenities ?? []);
        }

        $space->load(['photos', 'workspaces.workspaceType', 'amenities']);

        return new SpaceResource($space);
    }

    public function destroy(Request $request, Space $space)
    {
        abort_if($space->host_id !== $request->user()->id, 403);
        $space->delete();
        return response()->json(['message' => 'Space deleted']);
    }

    public function uploadPhoto(Request $request, Space $space)
    {
        abort_if($space->host_id !== $request->user()->id, 403);

        $request->validate([
            'photo' => 'required|image|max:5120|mimes:jpg,jpeg,png,webp',
        ]);

        $path = $request->file('photo')->store('spaces', 'public');

        $photo = $space->photos()->create([
            'path'     => $path,
            'order'    => $space->photos()->count(),
            'is_cover' => $space->photos()->count() === 0,
        ]);

        return response()->json(['photo' => $photo], 201);
    }

    public function deletePhoto(Request $request, Space $space, $photoId)
    {
        abort_if($space->host_id !== $request->user()->id, 403);
        $photo = $space->photos()->findOrFail($photoId);
        $photo->delete();
        return response()->json(['message' => 'Photo deleted']);
    }
}
