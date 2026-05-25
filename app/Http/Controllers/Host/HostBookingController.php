<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\SpaceWorkspace;
use Illuminate\Http\Request;

class HostBookingController extends Controller
{
    public function index(Request $request)
    {
        $workspaceIds = SpaceWorkspace::whereHas('space', function ($q) use ($request) {
            $q->where('host_id', $request->user()->id);
        })->pluck('id');

        $bookings = Booking::whereIn('space_workspace_id', $workspaceIds)
            ->with(['customer', 'workspace.workspaceType', 'workspace.space'])
            ->latest()
            ->paginate(20);

        return BookingResource::collection($bookings);
    }

    public function show(Request $request, Booking $booking)
    {
        abort_if(
            $booking->workspace->space->host_id !== $request->user()->id,
            403
        );

        $booking->load(['customer', 'workspace.workspaceType', 'workspace.space']);

        return new BookingResource($booking);
    }

    public function confirm(Request $request, Booking $booking)
    {
        abort_if(
            $booking->workspace->space->host_id !== $request->user()->id,
            403
        );

        $booking->update(['status' => 'confirmed']);

        return new BookingResource($booking);
    }

    public function cancel(Request $request, Booking $booking)
    {
        abort_if(
            $booking->workspace->space->host_id !== $request->user()->id,
            403
        );

        $booking->update(['status' => 'cancelled']);

        return new BookingResource($booking);
    }
}
