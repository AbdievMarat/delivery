<?php

namespace App\Services\Strategies;

use App\Services\Interfaces\RemainsStrategyInterface;
use SoapClient;
use SoapFault;

class KazakhstanRemainsStrategy implements RemainsStrategyInterface
{
    /**
     * @param array $products
     * @param string $shopMobileBackendId
     * @return array
     * @throws SoapFault
     */
    public function getRemains(array $products, string $shopMobileBackendId): array
    {
        $wsdl = env('KAZAKHSTAN_REMAINS_URL');
        $options = [
            'login' => env('LOGIN_KAZAKHSTAN_REMAINS'),
            'password' => env('PASSWORD_KAZAKHSTAN_REMAINS'),
        ];
        $client = new SoapClient($wsdl, $options);

        foreach ($products as $key => $product) {
            $params = [
                "sku" => $product['product_sku'], //"Р-000005733"
                "Tovar" => "",
                "Kol" => "",
                "ID" => $shopMobileBackendId //"1024"
            ];

            try {
                $response = $client->__soapCall("Start", [$params])->return;
                $data = json_decode($response)[0];

                $products[$key]['remainder'] = $data->Kol;
                $products[$key]['date_withdrawal_remains'] = $data->Date;
            } catch (SoapFault $e) {
                $products[$key]['remainder'] = null;
                $products[$key]['date_withdrawal_remains'] = null;
            }
        }

        return $products;
    }
}
