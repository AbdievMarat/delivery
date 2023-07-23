<?php

namespace App\Actions\Admin\Order;

use App\Enums\OrderStatus;
use App\Models\OrderDeliveryInYandex;
use App\Services\DeliveryYandex;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class GetDriverPositionYandex
{
    /**
     * @param $request
     * @return JsonResponse
     * @throws GuzzleException
     */
    public function __invoke($request): JsonResponse
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

        $driverPositions = [];

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

        return response()->json(['driver_positions' => $driverPositions]);
    }
}
