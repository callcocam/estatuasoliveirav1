<?php

namespace App\Support;

use App\Models\Setting;

class CompanyProfile
{
    /**
     * Build the public company profile shared with the site pages.
     *
     * @return array{name: string, about: string|null, phone: string|null, whatsapp: string|null, email: string|null, address: string|null}
     */
    public static function toArray(): array
    {
        $addressParts = array_filter([
            implode(', ', array_filter([Setting::get('address_street'), Setting::get('address_number')])),
            Setting::get('address_district'),
            implode(' - ', array_filter([Setting::get('address_city'), Setting::get('address_state')])),
            Setting::get('address_zip'),
        ]);

        return [
            'name' => Setting::get('company_name', 'Estátuas Oliveira'),
            'about' => Setting::get('company_about'),
            'phone' => Setting::get('contact_phone'),
            'whatsapp' => Setting::get('contact_whatsapp'),
            'email' => Setting::get('contact_email'),
            'address' => $addressParts === [] ? null : implode(' · ', $addressParts),
        ];
    }
}
