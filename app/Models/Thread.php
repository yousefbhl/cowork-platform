<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Thread extends Model
{
    protected $fillable = [
        'space_id', 'customer_id', 'host_id', 'last_message_at',
    ];

    public function messages()
    {
        return $this->hasMany(Message::class)->latest();
    }

    public function space()
    {
        return $this->belongsTo(Space::class);
    }
}
