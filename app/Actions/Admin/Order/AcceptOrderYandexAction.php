<?php

namespace App\Actions\Admin\Order;

use App\Models\Order;
use App\Models\OrderDeliveryInYandex;
use App\Models\OrderLog;
use App\Services\DeliveryYandex;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class AcceptOrderYandexAction
{
    public function __invoke($request): JsonResponse
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

            $orderLog = new OrderLog();
            $orderLog->order_id = $order->id;
            $orderLog->message = 'Заказ передан магазину';
            $orderLog->user_name = Auth::user()->name ?? '';
            $orderLog->user_id = Auth::id();
            $orderLog->save();

            return response()->json([]);
        } else {
            return response()->json(['error' => $responseYandexData], $responseYandex->getStatusCode());
        }
    }
}
