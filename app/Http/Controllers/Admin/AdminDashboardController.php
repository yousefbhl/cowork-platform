<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Space;
use App\Models\User;

class AdminDashboardController extends Controller
{
    public function index()
    {
        return response()->json([
            'users_count'     => User::count(),
            'spaces_count'    => Space::count(),
            'published_count' => Space::where('status', 'published')->count(),
            'bookings_count'  => Booking::count(),
            'revenue_total'   => Booking::where('status', 'confirmed')->sum('total_amount'),
        ]);
    }
}
