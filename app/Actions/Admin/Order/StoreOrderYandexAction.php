<?php

namespace App\Actions\Admin\Order;

use App\Models\Order;
use App\Models\OrderDeliveryInYandex;
use App\Services\DeliveryYandex;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class StoreOrderYandexAction
{
    public function __invoke($request): JsonResponse
    {
        $orderId = $request->get('order_id');

        $order = Order::findOrFail($orderId);
        $order->update($request->validated());

        $orderItems = $order->items()
            ->where('product_name', '!=', 'Доставка')
            ->get();
        $country = $order->country;
        $shop = $order->shop;

        $items = [];
        foreach ($orderItems as $item) {
            $items[] = [
                'cost_currency' => $country->currency_iso,
                'cost_value' => (string)$item->product_price,
                'droppof_point' => 1,// Идентификатор точки, куда нужно доставить товар. Должен соответствовать значению route_points[].point_id
                'pickup_point' => 2,// Идентификатор точки, откуда нужно забрать товар. Должен соответствовать значению route_points[].point_id
                'quantity' => $item->quantity,
                'size' => [
                    'height' => 0.002,
                    'length' => 0.002,
                    'width' => 0.002,
                ],
                'title' => $item->product_name,
                'weight' => 0.002,
            ];
        }

        $yandexOrderData['emergency_contact'] = [
            'name' => $country->organization_name,
            'phone' => $country->contact_phone
        ];
        $yandexOrderData['items'] = $items;
        $yandexOrderData['route_points'] = [
            [
                'address' => [
                    'comment' => "Доставка из магазина Куликовский <{$shop->name}>. Сообщите менеджеру, что заказ по доставке Яндекс.Такси. Назовите номер заказа <{$order->order_number}> и заберите продукцию.",
                    'coordinates' => [floatval($shop->longitude), floatval($shop->latitude)],
                    'fullname' => $shop->name
                ],
                'contact' => [
                    'name' => $country->organization_name,
                    'phone' => $country->contact_phone
                ],
                'point_id' => 2,// Идентификатор точки. Должен соответствовать значению c pickup_point
                'type' => 'source',// Точка отправления, где курьер забирает товар
                'visit_order' => 1,// Порядок посещения точки (нумерация с 1)
                'skip_confirmation' => true// Пропускать подтверждение через SMS в данной точке
            ],
            [
                'address' => [
                    'comment' => "Заказ оплачен безналично, при передаче заказа нельзя требовать с получателя деньги за доставку. {$order->comment_for_driver}",
                    'coordinates' => [floatval($order->longitude), floatval($order->latitude)],
                    'fullname' => $order->address,
                    'porch' => $order->entrance,// Подъезд
                    'sfloor' => $order->floor,// Этаж
                    'sflat' => $order->flat// Квартира
                ],
                'contact' => [
                    'name' => $order->client_name,
                    'phone' => '+' . $order->client_phone
                ],
                'point_id' => 1,// Идентификатор точки. Должен соответствовать значению c droppof_point
                'type' => 'destination',// Точка назначения, где курьер передает товар
                'visit_order' => 2,// // Порядок посещения точки (нумерация с 1)
                'skip_confirmation' => true// Пропускать подтверждение через SMS в данной точке
            ]
        ];
        $yandexOrderData['client_requirements'] = [
            'cargo_options' => ['auto_courier']// Курьер только на машине
        ];

        //отправляет заказ во все выбранные тарифы в справочнике стран
        foreach ($country->yandex_tariffs as $yandex_tariff) {
            $yandexOrderData['client_requirements']['taxi_class'] = $yandex_tariff;

            $yandex = new DeliveryYandex($country->id);
            $responseYandex = $yandex->createOrderYandex($yandexOrderData);
            $responseYandexData = json_decode($responseYandex->getContent(), true);

            if ($responseYandex->getStatusCode() == ResponseAlias::HTTP_CREATED) {
                $orderDeliveryInYandex = new OrderDeliveryInYandex();
                $orderDeliveryInYandex->order_id = $order->id;
                $orderDeliveryInYandex->yandex_id = $responseYandexData['id'];
                $orderDeliveryInYandex->shop_id = $order->shop_id;
                $orderDeliveryInYandex->shop_address = $shop->address;
                $orderDeliveryInYandex->shop_latitude = $shop->latitude;
                $orderDeliveryInYandex->shop_longitude = $shop->longitude;
                $orderDeliveryInYandex->client_address = $order->address;
                $orderDeliveryInYandex->client_latitude = $order->latitude;
                $orderDeliveryInYandex->client_longitude = $order->longitude;
                $orderDeliveryInYandex->tariff = $responseYandexData['client_requirements']['taxi_class'];
                $orderDeliveryInYandex->status = $responseYandexData['status'];
                $orderDeliveryInYandex->user_id = Auth::id();
                $orderDeliveryInYandex->save();
            } else {
                return response()->json(['error' => $responseYandexData], $responseYandex->getStatusCode());
            }
        }

        $count_of_orders_to_yandex_awaiting_estimate = $order->deliveryInYandex
            ->where('status', '!=', OrderDeliveryInYandex::YANDEX_STATUS_READY_FOR_APPROVAL)
            ->where('offer_price', '=', '0')
            ->count();

        return response()->json(['count_of_orders_to_yandex_awaiting_estimate' => $count_of_orders_to_yandex_awaiting_estimate], 201);
    }
}
