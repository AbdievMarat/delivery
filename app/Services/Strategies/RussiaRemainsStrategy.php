<?php

namespace App\Services\Strategies;

use App\Services\Interfaces\RemainsStrategyInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class RussiaRemainsStrategy implements RemainsStrategyInterface
{
    /**
     * @param array $products
     * @param string $shopMobileBackendId
     * @return array
     * @throws GuzzleException
     */
    public function getRemains(array $products, string $shopMobileBackendId): array
    {
        $client = new Client([
            'verify' => false // Опция для отключения проверки SSL-сертификата
        ]);

        $options = [
            'auth' => [
                env('LOGIN_RUSSIA_REMAINS'),
                env('PASSWORD_RUSSIA_REMAINS')
            ],
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded'
            ]
        ];

        foreach ($products as $key => $product) {
            // Отправляем GET-запрос
            $response = $client->get(env('RUSSIA_REMAINS_URL') . '/' . $product['product_sku'] . '/' . $shopMobileBackendId, $options);

            if ($response->getStatusCode() === ResponseAlias::HTTP_OK) {
                $body = $response->getBody()->getContents();
                $data = json_decode($body, true);

                $products[$key]['remainder'] = $data['Kol'];
            } else {
                $products[$key]['remainder'] = null;
            }

            // Устанавливаем значение 'date_withdrawal_remains' в null
            $products[$key]['date_withdrawal_remains'] = null;
        }

        return $products;
    }
}
