<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\SpaceWorkspace;
use Illuminate\Http\Request;

class HostDashboardController extends Controller
{
    public function index(Request $request)
    {
        $host = $request->user();

        $workspaceIds = SpaceWorkspace::whereHas('space', function ($q) use ($host) {
            $q->where('host_id', $host->id);
        })->pluck('id');

        return response()->json([
            'spaces_count'    => $host->spaces()->count(),
            'bookings_count'  => Booking::whereIn('space_workspace_id', $workspaceIds)->count(),
            'confirmed_count' => Booking::whereIn('space_workspace_id', $workspaceIds)
                                    ->where('status', 'confirmed')->count(),
            'revenue_total'   => Booking::whereIn('space_workspace_id', $workspaceIds)
                                    ->where('status', 'confirmed')
                                    ->sum('total_amount'),
        ]);
    }
}
