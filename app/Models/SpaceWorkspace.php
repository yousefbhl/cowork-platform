<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpaceWorkspace extends Model
{
    use HasFactory;

    protected $fillable = [
        'space_id', 'workspace_type_id', 'capacity',
        'price_hourly', 'price_daily', 'price_monthly',
        'currency', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price_hourly' => 'decimal:2',
        'price_daily' => 'decimal:2',
        'price_monthly' => 'decimal:2',
    ];

    public function space()
    {
        return $this->belongsTo(Space::class);
    }

    public function workspaceType()
    {
        return $this->belongsTo(WorkspaceType::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function bookingDays()
    {
        return $this->hasMany(BookingDay::class);
    }

    public function bookedDays(string $from, string $to): array
    {
        return $this->bookingDays()
            ->whereBetween('day', [$from, $to])
            ->pluck('day')
            ->map(fn($d) => (string) $d)
            ->toArray();
    }
}
