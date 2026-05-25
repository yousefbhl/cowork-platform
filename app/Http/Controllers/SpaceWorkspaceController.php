<?php

namespace App\Http\Controllers;

use App\Models\SpaceWorkspace;
use Illuminate\Http\Request;

class SpaceWorkspaceController extends Controller
{
    public function availability(SpaceWorkspace $workspace, Request $request)
    {
        $request->validate([
            'from' => 'required|date',
            'to'   => 'required|date|after:from',
        ]);

        $bookedDays = $workspace->bookedDays(
            $request->from,
            $request->to
        );

        return response()->json([
            'workspace_id' => $workspace->id,
            'from'         => $request->from,
            'to'           => $request->to,
            'booked_days'  => $bookedDays,
        ]);
    }
}
