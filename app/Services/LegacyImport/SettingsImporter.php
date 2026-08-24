<?php

namespace App\Services\LegacyImport;

use App\Models\Setting;

class SettingsImporter extends LegacyImporter
{
    public function handle(): void
    {
        $this->importCompany();
        $this->importAddress();
    }

    /**
     * Import the real company's data into `company.*`/`contact.*` settings.
     */
    private function importCompany(): void
    {
        $rows = $this->reader->rows('companies');
        $this->report->source('companies', count($rows));

        foreach ($rows as $row) {
            if ($row['id'] !== self::REAL_COMPANY_ID) {
                $this->report->skip('companies', $row['id'], 'outra empresa');

                continue;
            }

            foreach (array_filter([
                'company_name' => trim((string) $row['name']),
                'company_document' => $row['document'],
                'company_about' => $row['description'],
            ]) as $key => $value) {
                Setting::set($key, $value, 'company');
            }

            foreach (array_filter([
                'contact_email' => $row['email'],
                'contact_phone' => $row['phone'],
            ]) as $key => $value) {
                Setting::set($key, $value, 'contact');
            }

            $this->report->imported('companies');
        }
    }

    /**
     * Import the real company's address into `address.*` settings.
     */
    private function importAddress(): void
    {
        $rows = $this->reader->rows('address');
        $this->report->source('address', count($rows));

        foreach ($rows as $row) {
            $isCompanyAddress = ($row['addresable_type'] ?? null) === 'App\\Company'
                && ($row['addresable_id'] ?? null) === self::REAL_COMPANY_ID;

            if (! $isCompanyAddress) {
                $this->report->skip('address', $row['id'], 'endereço de usuário ou de outra empresa');

                continue;
            }

            foreach (array_filter([
                'address_street' => $row['street'],
                'address_number' => $row['number'],
                'address_complement' => $row['complement'],
                'address_district' => $row['district'],
                'address_city' => $row['city'],
                'address_state' => $row['state'],
                'address_zip' => $row['zip'],
                'address_country' => $row['country'] ? mb_convert_case(mb_strtolower($row['country']), MB_CASE_TITLE) : null,
            ]) as $key => $value) {
                Setting::set($key, $value, 'address');
            }

            $this->report->imported('address');
        }
    }
}
