<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class ProductUpdateRequest extends ProductStoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('product')) ?? false;
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
            Rule::unique('products', 'slug')->ignore($this->route('product')),
        ];

        return $rules;
    }
}
