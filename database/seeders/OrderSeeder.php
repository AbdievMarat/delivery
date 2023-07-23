<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderDeliveryInYandex;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Order::factory()->count(20)->has(
            OrderDeliveryInYandex::factory()->count(4),
            'deliveryInYandex'
        )->create();
    }
}
