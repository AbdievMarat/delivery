<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderSource;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class OrderController extends Controller
{
    /**
     * @param StoreOrderRequest $request
     * @return JsonResponse
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['source'] = OrderSource::MobileApp->value;
        $data['status'] = OrderStatus::New->value;

        $order = new Order($data);
        $order->save();

        $items = [];
        $itemsJson = json_decode($request->get('items'), true);
        foreach ($itemsJson as $item) {
            $items[] = [
                'product_name' => $item['product_name'],
                'product_sku' => $item['product_sku'],
                'product_price' => $item['product_price'],
                'quantity' => $item['quantity'],
            ];
        }

        if ($items) {
            $order->items()->createMany($items); // Создаем связанные модели массово
        }

        if ($order->wasRecentlyCreated) {
            $success = true;
            $message = 'Заказ создан';
            $data = new OrderResource($order);
            $status = ResponseAlias::HTTP_CREATED;
        } else {
            $success = false;
            $message = 'Заказ не создан';
            $data = [];
            $status = ResponseAlias::HTTP_INTERNAL_SERVER_ERROR;
        }

        return response()->json([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ], $status);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $order = Order::query()->where('order_number', '=', $id)->first();

        if ($order) {
            $success = true;
            $message = 'Данные заказа';
            $data = new OrderResource($order);
            $status = ResponseAlias::HTTP_OK;
        } else {
            $success = false;
            $message = 'Заказ не найдет';
            $data = [];
            $status = ResponseAlias::HTTP_INTERNAL_SERVER_ERROR;
        }

        return response()->json([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ], $status);
    }
}
