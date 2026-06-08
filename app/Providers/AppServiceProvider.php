<?php

namespace App\Providers;

use App\Filters\SpaceFilter;
use App\Models\Booking;
use App\Policies\BookingPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SpaceFilter::class, function () {
            return new SpaceFilter(request());
        });
    }

    public function boot(): void
    {
        Gate::policy(Booking::class, BookingPolicy::class);
    }
}
