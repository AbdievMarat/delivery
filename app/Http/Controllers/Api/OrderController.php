<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderSource;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

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
        if ($order->id) {
            $order = new OrderResource($order);
            return response()->json(['order' => $order], 201);
        } else {
            return response()->json([
                'success'   => false,
                'message'   => 'Заказ не создан',
                'data'      => []
            ], 404);
        }
    }

    /**
     * @param int $id
     * @return JsonResponse|OrderResource
     */
    public function show(int $id): JsonResponse|OrderResource
    {
        $order = Order::query()->where('order_number', '=', $id)->first();

        if ($order) {
            return new OrderResource($order);
        } else {
            return response()->json([
                'success'   => false,
                'message'   => 'Заказ не найдет',
                'data'      => []
            ], 404);
        }
    }
}
