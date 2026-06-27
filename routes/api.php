<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminSpaceController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\AmenityController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\WorkspaceTypeController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\Host\HostBookingController;
use App\Http\Controllers\Host\HostDashboardController;
use App\Http\Controllers\Host\HostSpaceController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SpaceController;
use App\Http\Controllers\SpaceWorkspaceController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // ── Auth ─────────────────────────────────────────────
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
    Route::post('/logout',   [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('/me',        [AuthController::class, 'me'])->middleware('auth:sanctum');

    // ── Public ───────────────────────────────────────────
    Route::get('/spaces',                                      [SpaceController::class, 'index']);
    Route::get('/spaces/{space}',                              [SpaceController::class, 'show']);
    Route::get('/spaces/{space}/reviews',                      [ReviewController::class, 'index']);
    Route::get('/workspaces/{workspace}/availability',         [SpaceWorkspaceController::class, 'availability']);
    Route::get('/amenities',                                   [AmenityController::class, 'index']);
    Route::get('/workspace-types',                             [WorkspaceTypeController::class, 'index']);

    // ── Customer ─────────────────────────────────────────
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/bookings',                                [BookingController::class, 'index']);
        Route::post('/bookings',                               [BookingController::class, 'store']);
        Route::get('/bookings/{booking}',                      [BookingController::class, 'show']);
        Route::patch('/bookings/{booking}/cancel',             [BookingController::class, 'cancel']);
        Route::post('/bookings/{booking}/review',              [ReviewController::class, 'store']);
        Route::get('/threads',                                 [MessageController::class, 'threads']);
        Route::get('/threads/{thread}',                        [MessageController::class, 'show']);
        Route::post('/threads/{thread}/messages',              [MessageController::class, 'send']);
        Route::post('/spaces/{space}/enquire',                 [MessageController::class, 'enquire']);
    });

    // ── Host ─────────────────────────────────────────────
    Route::middleware(['auth:sanctum', 'role:host'])
        ->prefix('host')
        ->group(function () {
            Route::get('/dashboard',                           [HostDashboardController::class, 'index']);
            Route::apiResource('spaces',                       HostSpaceController::class);
            Route::post('/spaces/{space}/photos',              [HostSpaceController::class, 'uploadPhoto']);
            Route::delete('/spaces/{space}/photos/{photoId}',  [HostSpaceController::class, 'deletePhoto']);
            Route::get('/bookings',                            [HostBookingController::class, 'index']);
            Route::get('/bookings/{booking}',                  [HostBookingController::class, 'show']);
            Route::patch('/bookings/{booking}/confirm',        [HostBookingController::class, 'confirm']);
            Route::patch('/bookings/{booking}/cancel',         [HostBookingController::class, 'cancel']);
            Route::get('/threads',                             [MessageController::class, 'hostThreads']);
        });

    // ── Admin ─────────────────────────────────────────────
    Route::middleware(['auth:sanctum', 'role:admin'])
        ->prefix('admin')
        ->group(function () {
            Route::get('/dashboard',                           [AdminDashboardController::class, 'index']);
            Route::apiResource('users',                        AdminUserController::class)->except(['create', 'edit']);
            Route::patch('/users/{user}/role',                 [AdminUserController::class, 'updateRole']);
            Route::get('/spaces',                              [AdminSpaceController::class, 'index']);
            Route::patch('/spaces/{space}/approve',            [AdminSpaceController::class, 'approve']);
            Route::patch('/spaces/{space}/reject',             [AdminSpaceController::class, 'reject']);
            Route::delete('/spaces/{space}',                   [AdminSpaceController::class, 'destroy']);
        });
});
