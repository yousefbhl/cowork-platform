<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpacePhoto extends Model
{
    protected $fillable = ['space_id', 'path', 'order', 'is_cover'];

    public function space()
    {
        return $this->belongsTo(Space::class);
    }
}
