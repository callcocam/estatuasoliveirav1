<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Seed the site settings with the company's real data.
     */
    public function run(): void
    {
        $settings = [
            'company' => [
                'company_name' => 'Estátuas Oliveira',
                'company_document' => '10.272.845-0001-90',
                'company_about' => 'Há mais de 25 anos no mercado nossa empresa "Estátuas Oliveira" se dedica a proporcionar o embelezamento de ambientes. Fabricamos vários itens em cimento, mármore e gesso para decorar sua casa, sítio e jardim. Além de uma infinidade de estátuas, vasos, fontes, bancos, mesas e decorações temos também opções em cerâmica. Venham conhecer nossos produtos em meio de um lugar agradável ao ar livre com muitas árvores e aquários naturais. Aguardamos a sua visita!',
            ],
            'contact' => [
                'contact_email' => 'contato@estatuasoliveira.com.br',
                'contact_phone' => '+55 51 99973-2078',
                'contact_whatsapp' => '5551999732078',
            ],
            'address' => [
                'address_street' => 'Estrada RS 030',
                'address_number' => '2745',
                'address_complement' => 'KM 80',
                'address_district' => 'Glória',
                'address_city' => 'Osório',
                'address_state' => 'RS',
                'address_zip' => '95520-000',
                'address_country' => 'Brasil',
            ],
        ];

        foreach ($settings as $group => $values) {
            foreach ($values as $key => $value) {
                Setting::set($key, $value, $group);
            }
        }
    }
}
