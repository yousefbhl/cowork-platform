<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\SpaceWorkspace;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingService
{
    /**
     * Create a booking with atomic double-booking protection.
     *
     * The booking_days UNIQUE(space_workspace_id, day) index is the guard.
     * If two requests race for the same day, the second INSERT throws a
     * duplicate-key error → we catch it → 422 conflict.
     */
    public function create(int $customerId, array $data): Booking
    {
        $workspace = SpaceWorkspace::findOrFail($data['space_workspace_id']);

        $days = $this->daysBetween($data['start_date'], $data['end_date']);

        if (empty($days)) {
            throw ValidationException::withMessages([
                'end_date' => ['Booking must cover at least one day.'],
            ]);
        }

        $total = $this->calculateTotal($workspace, count($days));

        return DB::transaction(function () use ($customerId, $workspace, $data, $days, $total) {

            // Lock the workspace row so concurrent bookings serialize
            SpaceWorkspace::where('id', $workspace->id)->lockForUpdate()->first();

            $booking = Booking::create([
                'customer_id'        => $customerId,
                'space_workspace_id' => $workspace->id,
                'start_date'         => $data['start_date'],
                'end_date'           => $data['end_date'],
                'status'             => 'pending',
                'total_amount'       => $total,
                'currency'           => $workspace->currency,
                'notes'              => $data['notes'] ?? null,
            ]);

            // Insert one row per day — duplicate key = double-booking caught
            try {
                $rows = array_map(fn ($day) => [
                    'booking_id'         => $booking->id,
                    'space_workspace_id' => $workspace->id,
                    'day'                => $day,
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ], $days);

                DB::table('booking_days')->insert($rows);
            } catch (\Illuminate\Database\QueryException $e) {
                if ($this->isDuplicateKey($e)) {
                    throw ValidationException::withMessages([
                        'start_date' => ['One or more of these dates are no longer available.'],
                    ]);
                }
                throw $e;
            }

            return $booking;
        });
    }

    /**
     * All dates in range, inclusive of start, exclusive of end (checkout day).
     */
    private function daysBetween(string $start, string $end): array
    {
        $period = CarbonPeriod::create($start, $end);
        $days   = [];

        foreach ($period as $date) {
            if ($date->toDateString() === $end) continue;
            $days[] = $date->toDateString();
        }

        return $days;
    }

    private function calculateTotal(SpaceWorkspace $workspace, int $dayCount): float
    {
        return round(($workspace->price_daily ?? 0) * $dayCount, 2);
    }

    private function isDuplicateKey(\Illuminate\Database\QueryException $e): bool
    {
        return str_contains($e->getMessage(), 'UNIQUE constraint failed')
            || ($e->errorInfo[1] ?? null) === 1062;
    }
}
