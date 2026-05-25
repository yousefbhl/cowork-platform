<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Space extends Model
{
    use HasFactory;

    protected $fillable = [
        'host_id', 'title', 'slug', 'description',
        'address', 'lat', 'lng', 'city', 'country',
        'status', 'rating_avg', 'reviews_count',
    ];

    protected static function booted(): void
    {
        static::creating(function (Space $space) {
            if (empty($space->slug)) {
                $space->slug = Str::slug($space->title) . '-' . Str::random(6);
            }
        });
    }

    public function host()
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    public function photos()
    {
        return $this->hasMany(SpacePhoto::class)->orderBy('order');
    }

    public function coverPhoto()
    {
        return $this->hasOne(SpacePhoto::class)->where('is_cover', true)->latestOfMany();
    }

    public function amenities()
    {
        return $this->belongsToMany(Amenity::class);
    }

    public function workspaces()
    {
        return $this->hasMany(SpaceWorkspace::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function threads()
    {
        return $this->hasMany(Thread::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeInCity($query, string $city)
    {
        return $query->where('city', 'like', "%{$city}%");
    }
}
