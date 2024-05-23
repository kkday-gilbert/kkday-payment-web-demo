<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use GibberishAES\GibberishAES;

use function Symfony\Component\String\u;

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
            function (array $list, array $pmchData) use ($currencyCode) {
                $method = $pmchData['method'];
                $list[$method] = [
                    'name' => $pmchData['name'],
                    'data' => $this->getPaymentData($method, $pmchData, $currencyCode),
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

    private function getPaymentData(string $paymentMethod, array $pmchData, $currencyCode)
    {
        $pmch_value = config('payment_channel.' . $paymentMethod);
        $paymentParams = Arr::get($pmch_value, 'custom_params', []);

        $json_body = [
            'is_3d' => Arr::get($pmch_value, 'is_3d', true),
            'pmch_oid' => Arr::get($pmch_value, 'pmch_oid', 1),
            'pay_currency' => $currencyCode,
            'pay_amount' => Arr::get($pmch_value, 'amount', 5),

            'return_url' => route('payment.result'),
            'cancel_url' => route('payment.result'),

            'payment_source_info' => [
                'source_type' => 'KKDAY',
                'source_ref_no' => strtoupper(Str::random(15)),
                'source_code' => 'WEB',
            ],
            'payer_info' => [
                'first_name' => 'Su',
                'last_name' => 'Iris',
                'phone' => '0912345678',
                'email' => 'susu.su@kkday.com',
            ],
            'member' => [
                'member_uuid' => (string)Str::uuid(),
                'risk_status' => '02',
                'ip' => '172.16.1.18',
                'register_email' => 'susu.su@kkday.com',
                'is_verified_email' => true,
                'register_time' => '2021-01-05 10:02:23',
                'login_channel' => 'KKDAY',
            ],
            'items' => [
                [
                    'prod_oid' => 1,
                    'prod_name' => 'Test Product',
                    'product_name' => 'Test Product 01',
                    'kkday_product_country_code_list' => ['A01-001'],
                    'category' => 'event',
                    'sub_categories' => ['eat', 'drink'],
                    'item_details' => [
                        [
                            'unit_price' => 100,
                            'unit_qty' => 2,
                        ],
                    ],
                    'region_info' => [
                        [
                            'country_code' => 'TW',
                            'city' => 'Taipei',
                        ],
                    ],
                    'use_date' => '2022-09-01',
                ],
            ],
            'notification_info' => [
                'notification_type' => '02',
                'notification_url' => 'https://the.test.url',
            ],
            'discount_info' => [
                'coupons' => [
                    [
                        'coupon_amount' => 10,
                        'coupon_code' => 'test1023',
                    ],
                ],
                'point_amount' => 23,
            ],
            'payment_param3' => 'test_session_id',
        ];

        $jsondata = [
            'lang_code' => 'zh-tw',
            'timestamp' => time(),
            'json' => $json_body + $paymentParams,
        ];


        $encodedJsonData = $this->encryptBody(json_encode($jsondata));

        return
            [
                'actionUrl' => config('url.kkday_payment_url') . '/' . Arr::get($pmchData, 'url', ''),
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
