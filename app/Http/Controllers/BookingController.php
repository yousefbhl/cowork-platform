<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $bookings = $request->user()
            ->bookings()
            ->with(['workspace.workspaceType', 'workspace.space', 'review'])
            ->latest()
            ->paginate(20);

        return BookingResource::collection($bookings);
    }

    public function store(StoreBookingRequest $request)
    {
        // Double-booking engine implemented in Phase 4
        return response()->json([
            'message' => 'Booking engine not yet implemented',
        ], 501);
    }

    public function show(Request $request, Booking $booking)
    {
        $this->authorize('view', $booking);

        $booking->load(['workspace.workspaceType', 'workspace.space', 'customer', 'review']);

        return new BookingResource($booking);
    }

    public function cancel(Request $request, Booking $booking)
    {
        $this->authorize('cancel', $booking);

        $booking->update(['status' => 'cancelled']);

        return new BookingResource($booking);
    }
}
