<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\UnsupportedCountryException;
use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Order;
use App\Models\Shop;
use App\Services\MobileApplicationBackend;
use App\Services\RemainsProduct;
use App\Services\Strategies\KazakhstanRemainsStrategy;
use App\Services\Strategies\RussiaRemainsStrategy;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ProductController extends Controller
{
    /**
     * @throws UnsupportedCountryException
     * @throws GuzzleException
     */
    public function search(Request $request): JsonResponse
    {
        $countryId = $request->get('country_id');
        $desiredProduct = $request->get('desired_product');

        $mobileApplicationBackend = new MobileApplicationBackend($countryId);
        $responseMobileApplicationBackend = $mobileApplicationBackend->productSearch($desiredProduct);
        $responseMobileApplicationBackendData = json_decode($responseMobileApplicationBackend->getContent(), true);

        if ($responseMobileApplicationBackend->getStatusCode() == ResponseAlias::HTTP_OK) {
            return response()->json($responseMobileApplicationBackendData);
        } else {
            return response()->json(['error' => $responseMobileApplicationBackendData], $responseMobileApplicationBackend->getStatusCode());
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws UnsupportedCountryException
     * @throws Exception
     */
    public function getRemainsProducts(Request $request): JsonResponse
    {
        $orderId = $request->get('order_id');
        $shopId = $request->get('shop_id');

        $order = Order::findOrFail($orderId);
        $shop = Shop::findOrFail($shopId);

        $countryId = $order->country_id;
        $shopMobileBackendId = $shop->mobile_backend_id;

        $products = $order
            ->items
            ->where('product_name', '!=', 'Доставка')
            ->map(function ($item) {
                return [
                    'product_sku' => $item->product_sku,
                    'product_name' => $item->product_name,
                    'product_price' => $item->product_price,
                    'quantity' => $item->quantity,
                ];
            })
            ->toArray();

        // Создание объекта стратегии для получения остатков
        $product = new RemainsProduct();
        $strategy = match ((int)$countryId) {
            Country::KAZAKHSTAN_COUNTRY_ID => new KazakhstanRemainsStrategy(),
            Country::RUSSIA_COUNTRY_ID => new RussiaRemainsStrategy(),
            default => throw new UnsupportedCountryException('Remains product not available for this country.')
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
