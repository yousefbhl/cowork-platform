<?php

namespace App\Http\Controllers;

use App\Http\Resources\AmenityResource;
use App\Models\Amenity;

class AmenityController extends Controller
{
    public function index()
    {
        return AmenityResource::collection(Amenity::all());
    }
}
