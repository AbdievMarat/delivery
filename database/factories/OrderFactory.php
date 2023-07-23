<?php

namespace Database\Factories;

use App\Enums\DeliveryMode;
use App\Enums\OrderSource;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_number' => $this->faker->unique()->numberBetween(222435646, 222535646),
            'mobile_backend_callback_url' => 'https://api-devkdk.kulikov.com/v2/partner/lia/lia-result',
            'client_phone' => $this->faker->regexify('/^77\d{9}$/'),
            'client_name' => $this->faker->name,
            'country_id' => $this->faker->numberBetween(2, 3),
            'address' => $this->faker->address,
            'latitude' => $this->faker->latitude,
            'longitude' => $this->faker->longitude,
            'entrance' => $this->faker->numberBetween(1, 10),
            'floor' => $this->faker->numberBetween(1, 10),
            'flat' => $this->faker->numberBetween(1, 10),
            'payment_status' => $this->faker->randomElement([PaymentStatus::Unpaid, PaymentStatus::Paid]),
            'comment_for_operator' => $this->faker->text,
            'operator_deadline_date' => $this->faker->dateTimeBetween('-1 week', '+1 week'),
            'comment_for_driver' => $this->faker->text,
            'source' => OrderSource::MobileApp->value,
            'delivery_mode' => DeliveryMode::SoonAsPossible,
            'delivery_date' => $this->faker->dateTimeBetween('-1 week', '+1 week'),
            'status' => OrderStatus::New,
        ];
    }
    public function configure(): OrderFactory
    {
        $items = [
            [
                'product_name' => 'Доставка',
                'product_sku' => 'delivery-kz',
                'product_price' => '1500.00',
            ],
            [
                'product_name' => 'Торт "Три шоколада" гранд плюс (1,500 кг)',
                'product_sku' => 'ЦБ-00070021',
                'product_price' => '11900.00',
            ],
            [
                'product_name' => 'Торт "Красный бархат" классик (0,600 кг)',
                'product_sku' => 'Р-000005729',
                'product_price' => '4500.00',
            ],
            [
                'product_name' => 'Торт "Смородинка" классик (0,650 кг)',
                'product_sku' => 'Р-000005736',
                'product_price' => '3900.00',
            ],
            [
                'product_name' => 'Торт "Медовик со сгущенкой" гранд (0,800 кг)',
                'product_sku' => 'Р-000005764',
                'product_price' => '4900.00',
            ],
            [
                'product_name' => 'Торт "Каприз" гранд (1,000 кг)',
                'product_sku' => 'Р-000005759',
                'product_price' => '5395.00',
            ],
        ];

        return $this->afterCreating(function (Order $order) use ($items) {
            $count = rand(2, 5);
            for ($i = 0; $i < $count; $i++) {
                $item = $items[$i];
                $orderItems[] = OrderItem::factory()->make([
                    'product_name' => $item['product_name'],
                    'product_sku' => $item['product_sku'],
                    'product_price' => $item['product_price'],
                    'quantity' => $this->faker->numberBetween(1, 3),
                ]);
            }
            $order->items()->saveMany($orderItems);

            // Получаем связанные модели OrderItem для данного заказа
            $orderItems = $order->items;

            // Рассчитываем сумму произведений полей price и count
            $orderPrice = $orderItems->sum(function (OrderItem $item) {
                return $item->product_price * $item->quantity;
            });

            // Рассчитываем сумму бонусов
            $payment_bonuses = rand(0, 1000);

            // Рассчитываем сумму наличных
            $payment_cash = $orderPrice - $payment_bonuses;

            $order->update([
                'order_price' => $orderPrice,
                'payment_cash' => $payment_cash,
                'payment_bonuses' => $payment_bonuses,
            ]);
        });
    }
}
