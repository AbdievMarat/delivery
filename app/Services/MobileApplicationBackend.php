<?php

namespace App\Services;

use App\Exceptions\UnsupportedCountryException;
use App\Models\Country;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class MobileApplicationBackend
{
    private string $tokenMobileBackend;
    private Client $client;
    private array $headers;

    /**
     * @param int $countryId
     * @throws UnsupportedCountryException
     */
    public function __construct(int $countryId)
    {
        $this->tokenMobileBackend = match ($countryId) {
            Country::KYRGYZSTAN_COUNTRY_ID => env('TOKEN_MOBILE_BACKEND_KG'),
            Country::KAZAKHSTAN_COUNTRY_ID => env('TOKEN_MOBILE_BACKEND_KZ'),
            Country::RUSSIA_COUNTRY_ID => env('TOKEN_MOBILE_BACKEND_RU'),
            default => throw new UnsupportedCountryException('Mobile backend not available for this country.'),
        };

        $this->client = new Client();

        $this->headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Authorization' => 'Basic ' . base64_encode($this->tokenMobileBackend . ':'),
        ];
    }

    /**
     * @param string $desiredProduct
     * @return JsonResponse
     * @throws GuzzleException
     */
    public function productSearch(string $desiredProduct): JsonResponse
    {
        try {
            $response = $this->client->get(env('API_BACKEND_URL') . '/v2/partner/products/product-search', [
                'headers' => $this->headers,
                'query' => [
                    'product' => $desiredProduct
                ]
            ]);

            if ($response->getStatusCode() === ResponseAlias::HTTP_OK) {
                $body = $response->getBody()->getContents();
                $data = json_decode($body, true);

                return response()->json($data);
            } else {
                return response()->json(['error' => 'Ошибка при выполнении запроса'], $response->getStatusCode());
            }
        } catch (Exception $e) {
            // Обработка ошибок GuzzleHttp\Client
            return response()->json(['error' => $e->getMessage()], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param int $orderId
     * @param int $status
     * @param int $statusPay
     * @return JsonResponse|void
     */
    public function updateStatus(int $orderId, int $status, int $statusPay)
    {
        try {
            $data = [
                'order_id' => $orderId,
                'status_order' => $status,
                'status_pay' => $statusPay,
            ];

            $response = $this->client->post(env('API_BACKEND_URL') . '/v2/partner/lia/lia-result', [
                'headers' => $this->headers,
                'form_params' => $data,
            ]);

            if ($response->getStatusCode() === ResponseAlias::HTTP_CREATED) {
                $body = $response->getBody()->getContents();
                $data = json_decode($body, true);

                return response()->json($data);
            }
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        } catch (GuzzleException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }
}
