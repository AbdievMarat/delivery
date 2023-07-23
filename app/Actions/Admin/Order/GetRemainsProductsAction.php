<?php

namespace App\Actions\Admin\Order;

use App\Models\Order;
use App\Models\Shop;
use App\Services\RemainsProduct;
use App\Services\Strategies\KazakhstanRemainsStrategy;
use App\Services\Strategies\RussiaRemainsStrategy;
use Illuminate\Http\JsonResponse;

class GetRemainsProductsAction
{
    public function __invoke($request): JsonResponse
    {
        $orderId = $request->get('order_id');
        $shopId = $request->get('shop_id');

        $order = Order::findOrFail($orderId);
        $shop = Shop::findOrFail($shopId);

        $countryId = $order->country_id;
        $shopMobileBackendId = $shop->mobile_backend_id;

        $products = $order->items->where('product_name', '!=', 'Доставка')->map(function ($item) {
            return [
                'product_sku' => $item->product_sku,
                'product_name' => $item->product_name,
                'product_price' => $item->product_price,
                'quantity' => $item->quantity,
            ];
        })->toArray();

        // Создание объекта стратегии для получения остатков
        $product = new RemainsProduct();
        $strategy = match ((int)$countryId) {
            2 => new KazakhstanRemainsStrategy(),
            3 => new RussiaRemainsStrategy(),
            default => throw new Exception("Неподдерживаемая страна")
        };
        $product->setStrategy($strategy);

        // Получение остатков продуктов
        $remainsProducts = $product->getRemains($products, $shopMobileBackendId);

        return response()->json([
            'table' => view('admin.orders.table-remains-products', [
                'remainsProducts' => $remainsProducts,
                'shop_name' => $shop->name,
                'date_withdrawal_remains' => $remainsProducts[array_key_first($remainsProducts)]['date_withdrawal_remains']
            ])->render()
        ]);
    }
}
