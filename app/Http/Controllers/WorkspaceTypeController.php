<?php

namespace App\Http\Controllers;

use App\Models\WorkspaceType;

class WorkspaceTypeController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => WorkspaceType::select('id', 'name', 'label')->get(),
        ]);
    }
}
