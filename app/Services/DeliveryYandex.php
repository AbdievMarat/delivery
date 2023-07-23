<?php

namespace App\Services;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class DeliveryYandex
{
    private string $tokenYandex;

    public function __construct(int $countryId)
    {
        $this->tokenYandex = match ($countryId) {
            2 => env('TOKEN_DELIVERY_YANDEX_KZ'),
            3 => env('TOKEN_DELIVERY_YANDEX_RU'),
            default => ''
        };
    }

    /**
     * @param array $yandexOrderData
     * @return JsonResponse|void
     * @throws GuzzleException
     */
    public function createOrderYandex(array $yandexOrderData)
    {
        try {
            $client = new Client();
            $response = $client->post('https://b2b.taxi.yandex.net/b2b/cargo/integration/v2/claims/create', [
                'headers' => [
                    'Accept-Language' => 'ru',
                    'Authorization' => 'Bearer ' . $this->tokenYandex,
                ],
                'query' => ['request_id' => Str::uuid()->toString()],
                'json' => $yandexOrderData,
            ]);

            if ($response->getStatusCode() === ResponseAlias::HTTP_OK) {
                $body = $response->getBody()->getContents();
                $data = json_decode($body, true);

                return response()->json($data, 201);
            } else if($response->getStatusCode() === ResponseAlias::HTTP_BAD_REQUEST) {
                $body = $response->getBody()->getContents();
                $data = json_decode($body, true);

                return response()->json(['error' => [$data['message']]], $response->getStatusCode());
            }
        } catch (Exception $e) {
            // Обработка ошибок GuzzleHttp\Client
            return response()->json(['error' => $e->getMessage()], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param string $yandexId
     * @return JsonResponse|void
     * @throws GuzzleException
     */
    public function acceptOrderYandex(string $yandexId)
    {
        $data['version'] = 1;

        try {
            $client = new Client();
            $response = $client->post('https://b2b.taxi.yandex.net/b2b/cargo/integration/v1/claims/accept', [
                'headers' => [
                    'Accept-Language' => 'ru',
                    'Authorization' => 'Bearer ' . $this->tokenYandex,
                ],
                'query' => ['claim_id' => $yandexId],
                'json' => $data,
            ]);

            if ($response->getStatusCode() === ResponseAlias::HTTP_OK) {
                $body = $response->getBody()->getContents();
                $data = json_decode($body, true);

                return response()->json($data);
            }
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param string $yandexId
     * @return JsonResponse|void
     * @throws GuzzleException
     */
    public function cancelInfoOrderYandex(string $yandexId)
    {
        try {
            $client = new Client();
            $response = $client->post('https://b2b.taxi.yandex.net/b2b/cargo/integration/v2/claims/cancel-info', [
                'headers' => [
                    'Accept-Language' => 'ru',
                    'Authorization' => 'Bearer ' . $this->tokenYandex,
                ],
                'query' => ['claim_id' => $yandexId],
            ]);

            if ($response->getStatusCode() === ResponseAlias::HTTP_OK) {
                $body = $response->getBody()->getContents();
                $data = json_decode($body, true);

                return response()->json($data);
            }
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param string $yandexId
     * @param string $cancelState
     * @return JsonResponse|void
     * @throws GuzzleException
     */
    public function cancelOrderYandex(string $yandexId, string $cancelState)
    {
        $data['version'] = 1;
        $data['cancel_state'] = $cancelState;

        try {
            $client = new Client();
            $response = $client->post('https://b2b.taxi.yandex.net/b2b/cargo/integration/v1/claims/cancel', [
                'headers' => [
                    'Accept-Language' => 'ru',
                    'Authorization' => 'Bearer ' . $this->tokenYandex,
                ],
                'query' => ['claim_id' => $yandexId],
                'json' => $data,
            ]);

            if ($response->getStatusCode() === ResponseAlias::HTTP_OK) {
                $body = $response->getBody()->getContents();
                $data = json_decode($body, true);

                return response()->json($data);
            }
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param string $yandexId
     * @return JsonResponse|void
     * @throws GuzzleException
     */
    public function getDriverPositionYandex(string $yandexId)
    {
        try {
            $client = new Client();
            $response = $client->get('https://b2b.taxi.yandex.net/b2b/cargo/integration/v1/claims/performer-position', [
                'headers' => [
                    'Accept-Language' => 'ru',
                    'Authorization' => 'Bearer ' . $this->tokenYandex,
                ],
                'query' => ['claim_id' => $yandexId],
            ]);

            if ($response->getStatusCode() === ResponseAlias::HTTP_OK) {
                $body = $response->getBody()->getContents();
                $data = json_decode($body, true);

                return response()->json($data);
            }
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param string $yandexId
     * @return JsonResponse|void
     * @throws GuzzleException
     */
    public function getDriverPhoneYandex(string $yandexId)
    {
        $data['claim_id'] = $yandexId;

        try {
            $client = new Client();
            $response = $client->post('https://b2b.taxi.yandex.net/b2b/cargo/integration/v1/driver-voiceforwarding', [
                'headers' => [
                    'Accept-Language' => 'ru',
                    'Authorization' => 'Bearer ' . $this->tokenYandex,
                ],
                'json' => $data,
            ]);

            if ($response->getStatusCode() === ResponseAlias::HTTP_OK) {
                $body = $response->getBody()->getContents();
                $data = json_decode($body, true);

                return response()->json($data);
            }
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param string $yandexId
     * @return JsonResponse|void
     * @throws GuzzleException
     */
    public function getOrderYandexInfo(string $yandexId)
    {
        try {
            $client = new Client();
            $response = $client->post('https://b2b.taxi.yandex.net/b2b/cargo/integration/v2/claims/info', [
                'headers' => [
                    'Accept-Language' => 'ru',
                    'Authorization' => 'Bearer ' . $this->tokenYandex,
                ],
                'query' => ['claim_id' => $yandexId],
            ]);

            if ($response->getStatusCode() === ResponseAlias::HTTP_OK) {
                $body = $response->getBody()->getContents();
                $data = json_decode($body, true);

                return response()->json($data);
            }
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
