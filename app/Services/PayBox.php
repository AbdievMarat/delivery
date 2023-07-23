<?php

namespace App\Services;

use App\Models\Order;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class PayBox
{
    private string $merchantId;
    private string $merchantSign;
    private string $currencyName;

    public function __construct(int $countryId)
    {
        $this->merchantId = match ($countryId) {
            1 => env('PAY_BOX_KG_MERCHANT_ID'),
            2 => env('PAY_BOX_KZ_MERCHANT_ID'),
            3 => env('PAY_BOX_RU_MERCHANT_ID'),
            default => ''
        };

        $this->merchantSign = match ($countryId) {
            1 => env('PAY_BOX_KG_MERCHANT_SIGN'),
            2 => env('PAY_BOX_KZ_MERCHANT_SIGN'),
            3 => env('PAY_BOX_RU_MERCHANT_SIGN'),
            default => ''
        };

        $this->currencyName = match ($countryId) {
            1 => 'KGS',
            2 => 'KZT',
            3 => 'RUB',
            default => ''
        };
    }

    /**
     * @param array $data
     * @param string $methodPath
     * @return string
     */
    private function generateSignature(array $data, string $methodPath): string
    {
        // Сортировка полей сообщения в алфавитном порядке
        ksort($data);

        // Добавление типа операции в начало массива
        array_unshift($data, $methodPath);

        $flattenedData = $this->flattenArray($data);

        // Добавление секретного ключа в конец массива
        $flattenedData[] = $this->merchantSign;

        $concatenatedData = implode(';', $flattenedData);

        return md5($concatenatedData);
    }

    /**
     * @param int $orderNumber
     * @param int $amountOfPayment
     * @param string $description
     * @param string $clientPhone
     * @param $orderItems
     * @return JsonResponse|mixed|void
     * @throws GuzzleException
     */
    public function initiatePayment(int $orderNumber, int $amountOfPayment, string $description, string $clientPhone, $orderItems)
    {
        $receipt_positions = [];
        foreach ($orderItems as $item) {
            $receipt_positions[] = [
                'count' => $item['quantity'], // Количество товара
                'name' => $item['product_name'], // Наименование товара
                'price' => $item['product_price'], // Цена за 1 ед. товара
                'tax_type' => 0, // Тип налога, 0 - Без налога
            ];
        }

        $requestData = [
            'pg_order_id' => $orderNumber, // Идентификатор платежа в системе мерчанта
            'pg_merchant_id' => $this->merchantId, // Идентификатор мерчанта в FreedomPay
            'pg_amount' => $amountOfPayment, // Сумма платежа в валюте pg_currency
            'pg_description' => $description, // Описание товара или услуги. Отображается покупателю в процессе платежа
            'pg_salt' => 'string delivery salt ' . microtime(), // Случайная строка
            'pg_currency' => $this->currencyName, // Валюта, в которой указана сумма
            'pg_result_url' => env('PAY_BOX_RESULT_URL'), // URL для сообщения о результате платежа. Вызывается после платежа в случае успеха или неудачи. Если параметр не указан, то берется из настроек магазина
            'pg_request_method' => 'POST', // метод вызова скриптов магазина pg_result_url, для передачи информации от платежного гейта
            'pg_user_phone' => $clientPhone, // Телефон пользователя (начиная с кода страны), необходим для идентификации покупателя
            'pg_language' => 'ru', // Язык платежных страниц на сайте FreedomPay
            'pg_receipt_positions' => $receipt_positions, // детали оплачиваемого заказа
        ];

        $signature = $this->generateSignature($requestData, 'init_payment.php');
        $requestData['pg_sig'] = $signature;

        try {
            $client = new Client();
            $response = $client->post(env('PAY_BOX_URL') . '/init_payment.php', [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'body' => http_build_query($requestData),
            ]);

            if ($response->getStatusCode() === ResponseAlias::HTTP_OK) {
                $body = $response->getBody()->getContents();
                $response_xml = simplexml_load_string($body, "SimpleXMLElement", LIBXML_NOCDATA);
                $response_json = json_encode($response_xml);

                return json_decode($response_json, true);
            }
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param int $payBoxId
     * @param int $paymentCash
     * @return JsonResponse|mixed|void
     * @throws GuzzleException
     */
    public function refundPayment(int $payBoxId, int $paymentCash)
    {
        $requestData = [
            'pg_merchant_id' => $this->merchantId, // Идентификатор мерчанта в FreedomPay
            'pg_payment_id' => $payBoxId, // Внутренний идентификатор платежа в системе FreedomPay
            'pg_refund_amount' => $paymentCash, // Сумма возврата. Если параметр не передан или передан 0, то возвращается вся сумма.
            'pg_salt' => 'string delivery salt ' . microtime(), // Случайная строка
        ];

        $signature = $this->generateSignature($requestData, 'revoke.php');
        $requestData['pg_sig'] = $signature;

        try {
            $client = new Client();
            $response = $client->post(env('PAY_BOX_URL') . '/revoke.php', [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'body' => http_build_query($requestData),
            ]);

            if ($response->getStatusCode() === ResponseAlias::HTTP_OK) {
                $body = $response->getBody()->getContents();
                $response_xml = simplexml_load_string($body, "SimpleXMLElement", LIBXML_NOCDATA);
                $response_json = json_encode($response_xml);

                return json_decode($response_json, true);
            }
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * рекурсивно "разглаживает" вложенные массивы, преобразуя их в плоский одноуровневый массив, где ключи состоят из исходных ключей с указанием вложенности через [ ]
     * @param array $array
     * @return array
     */
    private function flattenArray(array $array): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $flattened = $this->flattenArray($value);
                foreach ($flattened as $subKey => $subValue) {
                    $result[$key . '[' . $subKey . ']'] = $subValue;
                }
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }
}
