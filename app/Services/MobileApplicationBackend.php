<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class MobileApplicationBackend
{
    private string $tokenMobileBackend;

    public function __construct(int $countryId)
    {
        $this->tokenMobileBackend = match ($countryId) {
            1 => env('TOKEN_MOBILE_BACKEND_KG'),
            2 => env('TOKEN_MOBILE_BACKEND_KZ'),
            3 => env('TOKEN_MOBILE_BACKEND_RU'),
            default => ''
        };
    }

    /**
     * @param string $desiredProduct
     * @return JsonResponse
     * @throws GuzzleException
     */
    public function productSearch(string $desiredProduct): JsonResponse
    {
        try {
            $password_curl = '';

            $client = new Client();

            $response = $client->request('GET', env('API_BACKEND_URL') . '/v2/partner/products/product-search', [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Authorization' => 'Basic ' . base64_encode("$this->tokenMobileBackend:$password_curl")
                ],
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

    public function updateStatus(int $orderId, int $status, int $statusPay)
    {
        try {
            $password_curl = '';

            $data = [
                'order_id' => $orderId,
                'status_order' => $status,
                'status_pay' => $statusPay,
            ];

            $client = new Client();
            $response = $client->post(env('API_BACKEND_URL') . '/v2/partner/lia/lia-result', [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Authorization' => 'Basic ' . base64_encode("$this->tokenMobileBackend:$password_curl")
                ],
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
