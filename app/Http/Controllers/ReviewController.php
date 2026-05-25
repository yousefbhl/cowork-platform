<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Booking;
use App\Models\Space;

class ReviewController extends Controller
{
    public function index(Space $space)
    {
        $reviews = $space->reviews()
            ->with('customer')
            ->latest()
            ->paginate(10);

        return ReviewResource::collection($reviews);
    }

    public function store(StoreReviewRequest $request, Booking $booking)
    {
        abort_if($booking->customer_id !== $request->user()->id, 403);
        abort_if($booking->status !== 'completed', 422, 'Booking not completed yet');
        abort_if($booking->review()->exists(), 409, 'Review already submitted');

        $review = $booking->review()->create([
            'customer_id' => $request->user()->id,
            'space_id'    => $booking->workspace->space_id,
            'rating'      => $request->rating,
            'comment'     => $request->comment,
        ]);

        $review->load('customer');

        return new ReviewResource($review);
    }
}
