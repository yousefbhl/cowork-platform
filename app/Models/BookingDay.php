<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingDay extends Model
{
    protected $fillable = ['booking_id', 'space_workspace_id', 'day'];

    protected $casts = ['day' => 'date'];
}
