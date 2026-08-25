<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class CategoryUpdateRequest extends CategoryStoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('category')) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = parent::rules();
        $rules['slug'] = [
            'nullable',
            'string',
            'max:255',
            Rule::unique('categories', 'slug')->ignore($this->route('category')),
        ];

        return $rules;
    }
}
