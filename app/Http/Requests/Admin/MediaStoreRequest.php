<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MediaStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
            'mediable_type' => ['required', Rule::in(['product', 'slider', 'category'])],
            'mediable_id' => ['required', 'string'],
            'collection' => ['nullable', 'string', 'max:64'],
            'alt' => ['nullable', 'string', 'max:255'],
        ];
    }
}
