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
                'id' => 1,
                'name' => 'Кыргызстан',
                'currency_name' => 'сом',
                'currency_iso' => 'KGS',
                'organization_name' => 'Кондитерский Дом Куликовский',
                'contact_phone' => '+996312545555',
                'latitude' => '54.9832693',
                'longitude' => '82.8963831',
                'status' => CountryStatus::Inactive
            ],
            [
                'id' => 2,
                'name' => 'Казахстан',
                'currency_name' => 'тенге',
                'currency_iso' => 'KZT',
                'organization_name' => 'Куликовский Казахстан',
                'contact_phone' => '+77273647777',
                'latitude' => '43.2220146',
                'longitude' => '76.8512485',
                'yandex_tariffs' => json_decode('["courier","express"]', true),
                'status' => CountryStatus::Active
            ],
            [
                'id' => 3,
                'name' => 'Россия',
                'currency_name' => 'руб.',
                'currency_iso' => 'RUB',
                'organization_name' => 'Куликовский Новосибирск',
                'contact_phone' => '+73832021029',
                'latitude' => '42.8746212',
                'longitude' => '74.5697617',
                'yandex_tariffs' => json_decode('["express"]', true),
                'status' => CountryStatus::Active
            ]
        ];
        foreach ($countries as $country) {
            Country::factory()->state($country)->create();
        }
    }
}
