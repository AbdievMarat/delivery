<?php

namespace App\Actions\Admin\Order;

use App\Models\OrderDeliveryInYandex;
use App\Services\DeliveryYandex;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class CancelOrderYandexAction
{
    public function __invoke($request): JsonResponse
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

            return response()->json([]);
        } else {
            return response()->json(['error' => $responseYandexData], $responseYandex->getStatusCode());
        }
    }
}
