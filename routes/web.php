<?php

use Illuminate\Support\Facades\Route;

// Every URL goes to the SPA - Vue Router handles the rest
Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');
