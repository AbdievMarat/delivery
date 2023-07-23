<?php

namespace Database\Factories;

use App\Enums\ShopStatus;
use App\Enums\YandexTariff;
use App\Models\OrderDeliveryInYandex;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderDeliveryInYandex>
 */
class OrderDeliveryInYandexFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'yandex_id' => $this->faker->uuid,
            'shop_id' => Shop::all()->where('status', '=', ShopStatus::Active)->random(),
            'shop_address' => $this->faker->address,
            'shop_latitude' => $this->faker->latitude,
            'shop_longitude' => $this->faker->longitude,
            'client_address' => $this->faker->address,
            'client_latitude' => $this->faker->latitude,
            'client_longitude' => $this->faker->longitude,
            'tariff' => $this->faker->randomElement([YandexTariff::Courier, YandexTariff::Express]),
            'offer_price' => $this->faker->numberBetween(1000, 2000),
            'final_price' => $this->faker->numberBetween(1000, 2000),
            'driver_phone' => $this->faker->phoneNumber,
            'driver_phone_ext' => $this->faker->numberBetween(100, 200),
            'user_id' => User::all()->random(),
            'status' => OrderDeliveryInYandex::YANDEX_STATUS_NEW
        ];
    }
}
