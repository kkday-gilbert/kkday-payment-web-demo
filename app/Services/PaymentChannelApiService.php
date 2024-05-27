<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PaymentChannelApiService
{
    private static string $list_api_endpoint;

    public function __construct()
    {
        self::$list_api_endpoint = sprintf(
            '%s/%s',
            config('url.kkday_payment_url'),
            config('url.kkday_payment_list_endpoint')
        );
    }

    public function getAvailablePaymentChannels(array $params): array
    {
        $lang = $params['lang'];
        $currency = $params['currency'];

        $conditionList = [
            [
                'type' => '15',
                'value' => $currency
            ],
            [
                'type' => '11',
                'value' => $lang
            ],
            [
                "type" => "19",
                "value" => "MAIN_APP"
            ]
        ];

        $response = Http::get(self::$list_api_endpoint, [
            'lang_code' => $lang,
            'json' => [
                'condition_list' => $conditionList,
            ],
            'need_detail' => '1',
        ])->json();

        $availablePaymentChannel = array_map(function ($data) {
            $name = !empty($data['pmch_name']) ? $data['pmch_name'] : $data['payment_method'];
            $name = ucwords(strtolower(str_replace("_", " ", $name)));
            return [
                'id' => $data['pmch_oid'],
                'name' => $name,
                'url' => $data['pmch_pay_url'],
                'method' => strtolower($data['payment_method']),
                'is_3d' => $data['is_3d'] === '1',
            ];
        }, $response['data']['pmch_list']);

        return array_reduce(
            $availablePaymentChannel,
            function ($paymentWithUniqueMethod, $payment) {
                $method = $payment['method'];
                if (!array_key_exists($method, $paymentWithUniqueMethod)) {
                    $paymentWithUniqueMethod[$method] = $payment;
                }
                return $paymentWithUniqueMethod;
            },
            []
        );
    }

}
