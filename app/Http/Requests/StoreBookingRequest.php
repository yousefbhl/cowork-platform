<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'space_workspace_id' => 'required|exists:space_workspaces,id',
            'start_date'         => 'required|date|after_or_equal:today',
            'end_date'           => 'required|date|after:start_date',
            'notes'              => 'nullable|string|max:1000',
        ];
    }
}
