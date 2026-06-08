<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $bookings = $request->user()
            ->bookings()
            ->with(['workspace.workspaceType', 'workspace.space.photos', 'review'])
            ->latest()
            ->paginate(20);

        return BookingResource::collection($bookings);
    }

    public function store(StoreBookingRequest $request, BookingService $service)
    {
        $booking = $service->create($request->user()->id, $request->validated());

        $booking->load(['workspace.workspaceType', 'workspace.space']);

        return (new BookingResource($booking))
            ->response()
            ->setStatusCode(201);
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

        $booking->days()->delete();
        $booking->update(['status' => 'cancelled']);

        return new BookingResource($booking);
    }
}
