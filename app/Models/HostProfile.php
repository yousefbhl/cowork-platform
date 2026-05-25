<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HostProfile extends Model
{
    protected $fillable = [
        'user_id', 'business_name', 'description',
        'stripe_account_id', 'phone', 'website',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
