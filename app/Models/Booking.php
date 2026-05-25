<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id', 'space_workspace_id', 'start_date', 'end_date',
        'status', 'total_amount', 'currency', 'stripe_payment_intent_id', 'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function workspace()
    {
        return $this->belongsTo(SpaceWorkspace::class, 'space_workspace_id');
    }

    public function days()
    {
        return $this->hasMany(BookingDay::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }
}
