<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use GibberishAES\GibberishAES;

class PaymentController extends Controller
{
    public function mainPage(Request $request)
    {
        $langCode = $request->query('lang');
        $currencyCode = $request->query('currency');

        if (!$langCode || !$currencyCode) {
            $langCode = $langCode ?? 'zh-tw';
            $currencyCode = $currencyCode ?? 'TWD';
            Log::info('payment.query', [
                'lang' => $langCode,
                'currency' => $currencyCode,
            ]);
            return redirect(sprintf("%s?lang=%s&currency=%s", route('payment.main-page'), $langCode, $currencyCode));
        }

        $availablePaymentChannel = $this->getAvailablePaymentChannels($currencyCode, $langCode);

        $paymentList = array_reduce(
            $availablePaymentChannel,
            function (array $list, array $pmchData) use ($currencyCode, $langCode) {
                $method = $pmchData['method'];
                $list[$method] = [
                    'name' => $pmchData['name'],
                    'data' => $this->generatePaymentData($pmchData, $currencyCode, $langCode),
                ];
                return $list;
            },
            []
        );

        return view('payment', [
            'langCode' => $langCode,
            'currencyCode' => $currencyCode,
            'availablePaymentChannel' => $availablePaymentChannel,
            'paymentList' => $paymentList,
        ]);
    }

    public function result(Request $request)
    {
        $jsondata = $this->decryptBody(data_get($request->all(), 'jsondata'));
        Log::info('result.request', $jsondata);

        $isSuccess = $jsondata['metadata']['status'] === '0000';
        $error = $isSuccess ? null : $jsondata['metadata'];

        return view('payment_result', [
            'error' => $error,
        ]);
    }

    private function encryptBody(string $payload)
    {
        $key = config('aes.jsondata_key');
        return GibberishAES::enc($payload, $key);
    }

    private function decryptBody(string $payload)
    {
        $key = config('aes.jsondata_key');
        return json_decode(GibberishAES::dec($payload, $key), true);
    }

    private function getPaymentJsonBody(array $channel, string $currencyCode, string $lanCode)
    {
        $common = config('payment_channel.common');
        data_set($common, 'payment_source_info.source_ref_no', Str::random(15));
        data_set($common, 'member.member_uuid', Str::uuid()->toString());

        $channelKey = data_get($channel, 'method', '');
        $customParams = data_get(config('payment_channel'), $channelKey, []);
        $paymentData = array_merge($common, [
            'pay_currency' => $currencyCode,
            'is_3d' => data_get($channel, 'is_3d', true),
            'pmch_oid' => data_get($channel, 'id', 1),

            'return_url' => route('payment.result'),
            'cancel_url' => route('payment.result'),

            'custom_params' => $customParams,
        ]);

        return [
            'lang_code' => $lanCode,
            'timestamp' => time(),
            'json' => $paymentData
        ];
    }

    private function generatePaymentData(array $channel, string $currencyCode, string $langCode)
    {
        $jsonData = $this->getPaymentJsonBody($channel, $currencyCode, $langCode);
        $encodedJsonData = $this->encryptBody(json_encode($jsonData));

        return
            [
                'actionUrl' => config('url.kkday_payment_url') . '/' . Arr::get($channel, 'url', ''),
                'body' => $encodedJsonData
            ];
    }

    private function getAvailablePaymentChannels(string $currencyCode, string $langCode)
    {
        $conditionList = [
            [
                'type' => '15',
                'value' => $currencyCode
            ],
            [
                'type' => '11',
                'value' => $langCode
            ]
        ];

        $list_api_endpoint = sprintf(
            '%s/%s',
            config('url.kkday_payment_url'),
            config('url.kkday_payment_list_endpoint')
        );
        $response = Http::get($list_api_endpoint, [
            'lang_code' => $langCode,
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
                'is_3d' => $data['is_3d'] === "1",
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
