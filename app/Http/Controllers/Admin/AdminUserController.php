<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function index()
    {
        return UserResource::collection(User::latest()->paginate(30));
    }

    public function show(User $user)
    {
        return new UserResource($user);
    }

    public function updateRole(Request $request, User $user)
    {
        $request->validate(['role' => 'required|in:customer,host,admin']);
        $user->update(['role' => $request->role]);
        return new UserResource($user);
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['message' => 'User deleted']);
    }
}
