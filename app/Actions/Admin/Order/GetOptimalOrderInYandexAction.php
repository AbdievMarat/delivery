<?php

namespace App\Actions\Admin\Order;

use App\Models\OrderDeliveryInYandex;
use App\Services\DeliveryYandex;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class GetOptimalOrderInYandexAction
{
    public function __invoke($request): JsonResponse
    {
        $orderId = $request->get('order_id');

        $ordersToYandexAwaitingEstimate = OrderDeliveryInYandex::query()
            ->select('order_delivery_in_yandex.id', 'order_delivery_in_yandex.yandex_id', 'order_delivery_in_yandex.status', 'orders.country_id', 'order_delivery_in_yandex.offer_price')
            ->join('orders', 'order_delivery_in_yandex.order_id', '=', 'orders.id')
            ->where('order_delivery_in_yandex.order_id', '=', $orderId)
            ->whereNotIn('order_delivery_in_yandex.status', [OrderDeliveryInYandex::YANDEX_STATUS_READY_FOR_APPROVAL, OrderDeliveryInYandex::YANDEX_STATUS_CANCELLED])
            ->where('offer_price', '=', '0')
            ->get()
            ->toArray();

        $maxOffer = null;
        $maxOfferId = null;
        $maxOfferYandexId = null;
        $maxOfferCountryId = null;

        foreach ($ordersToYandexAwaitingEstimate as $yandexOrder) {
            $yandex = new DeliveryYandex($yandexOrder['country_id']);
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
                    $maxOfferCountryId = $yandexOrder['country_id'];
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

        $count_of_orders_to_yandex_awaiting_estimate = OrderDeliveryInYandex::where('order_id', '=', $orderId)
            ->whereNotIn('status', [OrderDeliveryInYandex::YANDEX_STATUS_READY_FOR_APPROVAL, OrderDeliveryInYandex::YANDEX_STATUS_CANCELLED])
            ->where('offer_price', '=', '0')
            ->count();

        return response()->json(['count_of_orders_to_yandex_awaiting_estimate' => $count_of_orders_to_yandex_awaiting_estimate]);
    }
}
