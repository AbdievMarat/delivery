<?php

namespace App\Actions\Admin\Order;

use App\Services\MobileApplicationBackend;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ProductSearchAction
{
    /**
     * @throws GuzzleException
     */
    public function __invoke($request): JsonResponse
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
}
