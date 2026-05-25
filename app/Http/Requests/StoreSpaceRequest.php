<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSpaceRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'address'     => 'nullable|string|max:500',
            'lat'         => 'nullable|numeric|between:-90,90',
            'lng'         => 'nullable|numeric|between:-180,180',
            'city'        => 'nullable|string|max:100',
            'country'     => 'nullable|string|max:100',
            'amenities'   => 'nullable|array',
            'amenities.*' => 'exists:amenities,id',
        ];
    }
}
