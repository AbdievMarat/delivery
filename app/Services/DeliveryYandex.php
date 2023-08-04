<?php

namespace App\Services;

use App\Exceptions\UnsupportedCountryException;
use App\Models\Country;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class DeliveryYandex
{
    private string $tokenYandex;
    private Client $client;
    private array $headers;

    /**
     * @param int $countryId
     * @throws UnsupportedCountryException
     */
    public function __construct(int $countryId)
    {
        $this->tokenYandex = match ($countryId) {
            Country::KAZAKHSTAN_COUNTRY_ID => env('TOKEN_DELIVERY_YANDEX_KZ'),
            Country::RUSSIA_COUNTRY_ID => env('TOKEN_DELIVERY_YANDEX_RU'),
            default => throw new UnsupportedCountryException('Delivery yandex not available for this country.')
        };

        $this->client = new Client();
        $this->headers = [
            'Accept-Language' => 'ru',
            'Authorization' => 'Bearer ' . $this->tokenYandex,
        ];
    }

    /**
     * @param array $yandexOrderData
     * @return JsonResponse|void
     * @throws GuzzleException
     */
    public function createOrderYandex(array $yandexOrderData)
    {
        try {
            $response = $this->client->post('https://b2b.taxi.yandex.net/b2b/cargo/integration/v2/claims/create', [
                'headers' => $this->headers,
                'query' => ['request_id' => Str::uuid()->toString()],
                'json' => $yandexOrderData,
            ]);

            if ($response->getStatusCode() === ResponseAlias::HTTP_OK) {
                $body = $response->getBody()->getContents();
                $data = json_decode($body, true);

                return response()->json($data, ResponseAlias::HTTP_CREATED);
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
            $response = $this->client->post('https://b2b.taxi.yandex.net/b2b/cargo/integration/v1/claims/accept', [
                'headers' => $this->headers,
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
            $response = $this->client->post('https://b2b.taxi.yandex.net/b2b/cargo/integration/v2/claims/cancel-info', [
                'headers' => $this->headers,
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
            $response = $this->client->post('https://b2b.taxi.yandex.net/b2b/cargo/integration/v1/claims/cancel', [
                'headers' => $this->headers,
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
            $response = $this->client->get('https://b2b.taxi.yandex.net/b2b/cargo/integration/v1/claims/performer-position', [
                'headers' => $this->headers,
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
            $response = $this->client->post('https://b2b.taxi.yandex.net/b2b/cargo/integration/v1/driver-voiceforwarding', [
                'headers' => $this->headers,
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
            $response = $this->client->post('https://b2b.taxi.yandex.net/b2b/cargo/integration/v2/claims/info', [
                'headers' => $this->headers,
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
