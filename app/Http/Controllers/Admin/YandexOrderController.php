<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Exceptions\UnsupportedCountryException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreOrderYandexRequest;
use App\Models\Order;
use App\Models\OrderDeliveryInYandex;
use App\Services\DeliveryYandex;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class YandexOrderController extends Controller
{
    /**
     * @param StoreOrderYandexRequest $request
     * @param Order $order
     * @return JsonResponse
     * @throws GuzzleException
     * @throws UnsupportedCountryException
     */
    public function storeOrderYandex(StoreOrderYandexRequest $request, Order $order): JsonResponse
    {
        $order->update($request->validated());

        $orderItems = $order
            ->items()
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
                $order->deliveryInYandex()->create([
                    'yandex_id' => $responseYandexData['id'],
                    'shop_id' => $order->shop_id,
                    'shop_address' => $shop->address,
                    'shop_latitude' => $shop->latitude,
                    'shop_longitude' => $shop->longitude,
                    'client_address' => $order->address,
                    'client_latitude' => $order->latitude,
                    'client_longitude' => $order->longitude,
                    'tariff' => $responseYandexData['client_requirements']['taxi_class'],
                    'status' => $responseYandexData['status'],
                    'user_id' => Auth::id(),
                ]);
            } else {
                return response()->json(['error' => $responseYandexData], $responseYandex->getStatusCode());
            }
        }

        $count_of_orders_to_yandex_awaiting_estimate = $order->deliveryInYandex
            ->where('status', '!=', OrderDeliveryInYandex::YANDEX_STATUS_READY_FOR_APPROVAL)
            ->where('offer_price', '=', '0')
            ->count();

        return response()->json(['count_of_orders_to_yandex_awaiting_estimate' => $count_of_orders_to_yandex_awaiting_estimate], ResponseAlias::HTTP_CREATED);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws GuzzleException
     * @throws UnsupportedCountryException
     */
    public function cancelInfoOrderYandex(Request $request): JsonResponse
    {
        $orderDeliveryInYandexId = $request->get('order_delivery_in_yandex_id');
        $countryId = $request->get('country_id');

        $orderDeliveryInYandex = OrderDeliveryInYandex::findOrFail($orderDeliveryInYandexId);

        $yandex = new DeliveryYandex($countryId);
        $responseYandex = $yandex->cancelInfoOrderYandex($orderDeliveryInYandex->yandex_id);
        $responseYandexData = json_decode($responseYandex->getContent(), true);

        if ($responseYandex->getStatusCode() == ResponseAlias::HTTP_OK) {
            return response()->json(['cancel_state' => $responseYandexData['cancel_state']]);
        } else {
            return response()->json(['error' => $responseYandexData], $responseYandex->getStatusCode());
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws GuzzleException
     * @throws UnsupportedCountryException
     */
    public function cancelOrderYandex(Request $request): JsonResponse
    {
        $orderDeliveryInYandexId = $request->get('order_delivery_in_yandex_id');
        $countryId = $request->get('country_id');
        $cancelState = $request->get('cancel_state');

        $orderDeliveryInYandex = OrderDeliveryInYandex::findOrFail($orderDeliveryInYandexId);

        $yandex = new DeliveryYandex($countryId);
        $responseYandex = $yandex->cancelOrderYandex($orderDeliveryInYandex->yandex_id, $cancelState);
        $responseYandexData = json_decode($responseYandex->getContent(), true);

        if ($responseYandex->getStatusCode() == ResponseAlias::HTTP_OK) {
            $orderDeliveryInYandex->status = $responseYandexData['status'];
            $orderDeliveryInYandex->update();

            // если платно, записываем итоговую стоимость
            if($cancelState === 'paid') {
                $responseYandex = $yandex->getOrderYandexInfo($orderDeliveryInYandex->yandex_id);
                $responseYandexData = json_decode($responseYandex->getContent(), true);

                if ($responseYandexData['pricing']['final_price']) {
                    $orderDeliveryInYandex->final_price = $responseYandexData['pricing']['final_price'];
                    $orderDeliveryInYandex->status = $responseYandexData['status'];
                    $orderDeliveryInYandex->update();
                }
            }

            return response()->json();
        } else {
            return response()->json(['error' => $responseYandexData], $responseYandex->getStatusCode());
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws GuzzleException
     * @throws UnsupportedCountryException
     */
    public function acceptOrderYandex(Request $request): JsonResponse
    {
        $orderDeliveryInYandexId = $request->get('order_delivery_in_yandex_id');
        $countryId = $request->get('country_id');

        $orderDeliveryInYandex = OrderDeliveryInYandex::findOrFail($orderDeliveryInYandexId);

        $yandex = new DeliveryYandex($countryId);
        $responseYandex = $yandex->acceptOrderYandex($orderDeliveryInYandex->yandex_id);
        $responseYandexData = json_decode($responseYandex->getContent(), true);

        if ($responseYandex->getStatusCode() == ResponseAlias::HTTP_OK) {
            $orderDeliveryInYandex->status = $responseYandexData['status'];
            $orderDeliveryInYandex->update();

            $order = Order::findOrFail($orderDeliveryInYandex->order_id);
            $order->transferOrderToShop($orderDeliveryInYandex->shop_id);

            $order->logs()->create([
                'message' => 'Заказ передан магазину',
                'user_name' => Auth::user()->name,
                'user_id' => Auth::id(),
            ]);

            return response()->json();
        } else {
            return response()->json(['error' => $responseYandexData], $responseYandex->getStatusCode());
        }
    }

    /**
     * @param Order $order
     * @return JsonResponse
     */
    public function getOrdersInYandex(Order $order): JsonResponse
    {
        $order->load('deliveryInYandex.user');
        $ordersInYandex = $order->deliveryInYandex;
        $deliveryPrice = $order
            ->items()
            ->where('product_name', '=', 'Доставка')
            ->pluck('product_price')
            ->first();

        return response()->json([
            'content' => view('admin.orders.orders-in-yandex', [
                'ordersInYandex' => $ordersInYandex,
                'deliveryPrice' => $deliveryPrice
            ])->render()
        ]);
    }

    /**
     * берёт заказы которые ожидают оценки и отменяет заказ, который дороже по стоимости
     * @param Order $order
     * @return JsonResponse
     * @throws GuzzleException
     * @throws UnsupportedCountryException
     */
    public function getOptimalOrderInYandex(Order $order): JsonResponse
    {
        $ordersToYandexAwaitingEstimate = $order
            ->deliveryInYandex()
            ->select('id', 'yandex_id', 'status', 'offer_price')
            ->whereNotIn('status', [OrderDeliveryInYandex::YANDEX_STATUS_READY_FOR_APPROVAL, OrderDeliveryInYandex::YANDEX_STATUS_CANCELLED])
            ->where('offer_price', '=', '0')
            ->get()
            ->toArray();

        $maxOffer = null;
        $maxOfferId = null;
        $maxOfferYandexId = null;
        $maxOfferCountryId = null;

        foreach ($ordersToYandexAwaitingEstimate as $yandexOrder) {
            $yandex = new DeliveryYandex($order->country_id);
            $responseYandex = $yandex->getOrderYandexInfo($yandexOrder['yandex_id']);

            if ($responseYandex->getStatusCode() == ResponseAlias::HTTP_OK) {
                $responseYandexData = json_decode($responseYandex->getContent(), true);

                if($responseYandexData['pricing'] && $responseYandexData['pricing']['offer']) {
                    $offerPrice = $responseYandexData['pricing']['offer']['price'];
                } else {
                    $offerPrice = 100000;
                }

                if ($yandexOrder['status'] != $responseYandexData['status']) {
                    $orderDeliveryInYandex = OrderDeliveryInYandex::findOrFail($yandexOrder['id']);
                    $orderDeliveryInYandex->status = $responseYandexData['status'];
                    $orderDeliveryInYandex->update();

                    if($yandexOrder['offer_price'] != $offerPrice && $offerPrice != 100000){
                        $orderDeliveryInYandex->offer_price = $offerPrice;
                        $orderDeliveryInYandex->update();
                    }
                }

                if ($maxOffer === null || $offerPrice > $maxOffer) {
                    $maxOffer = $offerPrice;
                    $maxOfferId = $yandexOrder['id'];
                    $maxOfferYandexId = $yandexOrder['yandex_id'];
                    $maxOfferCountryId = $order->country_id;
                }
            }
        }

        if(count($ordersToYandexAwaitingEstimate) > 1 && $maxOfferId && $maxOfferYandexId && $maxOfferCountryId){
            $yandex = new DeliveryYandex($maxOfferCountryId);
            $responseYandex = $yandex->cancelOrderYandex($maxOfferYandexId, 'free');
            $responseYandexData = json_decode($responseYandex->getContent(), true);

            if ($responseYandex->getStatusCode() == ResponseAlias::HTTP_OK) {
                $orderDeliveryInYandex = OrderDeliveryInYandex::findOrFail($maxOfferId);
                $orderDeliveryInYandex->status = $responseYandexData['status'];
                $orderDeliveryInYandex->update();
            }
        }

        $count_of_orders_to_yandex_awaiting_estimate = $order
            ->deliveryInYandex()
            ->whereNotIn('status', [OrderDeliveryInYandex::YANDEX_STATUS_READY_FOR_APPROVAL, OrderDeliveryInYandex::YANDEX_STATUS_CANCELLED])
            ->where('offer_price', '=', '0')
            ->count();

        return response()->json(['count_of_orders_to_yandex_awaiting_estimate' => $count_of_orders_to_yandex_awaiting_estimate]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws GuzzleException
     * @throws UnsupportedCountryException
     */
    public function getDriverPositionYandex(Request $request): JsonResponse
    {
        $orderId = $request->get('order_id');
        $countryId = $request->get('country_id');

        $yandexOrder = OrderDeliveryInYandex::query()
            ->select('order_delivery_in_yandex.yandex_id')
            ->join('orders', 'order_delivery_in_yandex.order_id', '=', 'orders.id')
            ->where('order_delivery_in_yandex.order_id', '=', $orderId)
            ->whereIn('order_delivery_in_yandex.status', [
                OrderDeliveryInYandex::YANDEX_STATUS_DELIVERED,
                OrderDeliveryInYandex::YANDEX_STATUS_DELIVERY_ARRIVED,
                OrderDeliveryInYandex::YANDEX_STATUS_PERFORMER_FOUND,
                OrderDeliveryInYandex::YANDEX_STATUS_PICKUP_ARRIVED,
                OrderDeliveryInYandex::YANDEX_STATUS_PICKUPED,
            ])
            ->whereIn('orders.status', [
                OrderStatus::InShop,
                OrderStatus::AtDriver
            ])
            ->first();

        if($yandexOrder) {
            $yandex = new DeliveryYandex($countryId);
            $responseYandex = $yandex->getDriverPositionYandex($yandexOrder->yandex_id);

            if ($responseYandex->getStatusCode() == ResponseAlias::HTTP_OK) {
                $responseYandexData = json_decode($responseYandex->getContent(), true);

                if ($responseYandexData['position'] && $responseYandexData['position']['lat'] && $responseYandexData['position']['lon']) {
                    $driverPositions = [
                        'latitude' => $responseYandexData['position']['lat'],
                        'longitude' => $responseYandexData['position']['lon'],
                    ];
                }
            }
        }

        return response()->json(['driver_positions' => $driverPositions ?? []]);
    }
}
