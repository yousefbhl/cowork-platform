<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSpaceRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title'       => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'address'     => 'nullable|string|max:500',
            'lat'         => 'nullable|numeric|between:-90,90',
            'lng'         => 'nullable|numeric|between:-180,180',
            'city'        => 'nullable|string|max:100',
            'country'     => 'nullable|string|max:100',
            'status'      => 'sometimes|in:draft,published,paused',
            'amenities'   => 'nullable|array',
            'amenities.*' => 'exists:amenities,id',
        ];
    }
}
