<?php

namespace App\Http\Requests\Admin;

use App\Enums\SiteTheme;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SiteSettingsRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'company_name' => ['required', 'string', 'max:255'],
            'company_document' => ['nullable', 'string', 'max:30'],
            'company_about' => ['nullable', 'string', 'max:5000'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'contact_whatsapp' => ['nullable', 'string', 'max:30'],
            'contact_email' => ['nullable', 'string', 'email', 'max:255'],
            'address_street' => ['nullable', 'string', 'max:255'],
            'address_number' => ['nullable', 'string', 'max:30'],
            'address_complement' => ['nullable', 'string', 'max:255'],
            'address_district' => ['nullable', 'string', 'max:255'],
            'address_city' => ['nullable', 'string', 'max:255'],
            'address_state' => ['nullable', 'string', 'max:2'],
            'address_zip' => ['nullable', 'string', 'max:20'],
            'content_terms' => ['nullable', 'string', 'max:50000'],
            'site_default_theme' => ['required', Rule::enum(SiteTheme::class)],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,webp,svg', 'max:2048'],
        ];
    }
}
