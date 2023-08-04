<?php

namespace Database\Seeders;

use App\Enums\CountryStatus;
use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = [
            [
                'id' => Country::KYRGYZSTAN_COUNTRY_ID,
                'name' => 'Кыргызстан',
                'currency_name' => 'сом',
                'currency_iso' => 'KGS',
                'organization_name' => 'Кондитерский Дом Куликовский',
                'contact_phone' => '+996312545555',
                'status' => CountryStatus::Inactive
            ],
            [
                'id' => Country::KAZAKHSTAN_COUNTRY_ID,
                'name' => 'Казахстан',
                'currency_name' => 'тенге',
                'currency_iso' => 'KZT',
                'organization_name' => 'Куликовский Казахстан',
                'contact_phone' => '+77273647777',
                'yandex_tariffs' => '["courier","express"]',
                'status' => CountryStatus::Active
            ],
            [
                'id' => Country::RUSSIA_COUNTRY_ID,
                'name' => 'Россия',
                'currency_name' => 'руб.',
                'currency_iso' => 'RUB',
                'organization_name' => 'Куликовский Новосибирск',
                'contact_phone' => '+73832021029',
                'yandex_tariffs' => '["express"]',
                'status' => CountryStatus::Active
            ]
        ];
        foreach ($countries as $country) {
            Country::factory()->state($country)->create();
        }
    }
}
