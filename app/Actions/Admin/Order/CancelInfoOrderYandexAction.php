<?php

namespace App\Actions\Admin\Order;

use App\Models\OrderDeliveryInYandex;
use App\Services\DeliveryYandex;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class CancelInfoOrderYandexAction
{
    public function __invoke($request): JsonResponse
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
}
