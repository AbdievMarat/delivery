<?php

namespace App\Actions\Admin\Order;

use App\Enums\ShopStatus;
use App\Models\Shop;
use Illuminate\Http\JsonResponse;

class GetShopsOfCountryAction
{
    public function __invoke($request): JsonResponse
    {
        $countryId = $request->get('country_id');

        $shops = Shop::query()
            ->where('country_id', '=', $countryId)
            ->where('status', '=', ShopStatus::Active)
            ->where('latitude', '!=', '')
            ->where('longitude', '!=', '')
            ->get()
            ->toArray();

        return response()->json(['shops' => $shops]);
    }
}
