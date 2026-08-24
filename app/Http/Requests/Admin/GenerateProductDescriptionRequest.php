<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class GenerateProductDescriptionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'reference' => ['nullable', 'string', 'max:255'],
            'width_cm' => ['nullable', 'integer', 'min:0'],
            'height_cm' => ['nullable', 'integer', 'min:0'],
            'weight_kg' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
