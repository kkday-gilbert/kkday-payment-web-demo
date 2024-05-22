<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
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

        $availablePaymentList = [];

        if ($langCode === 'zh-tw' && $currencyCode === 'TWD') {
            $availablePaymentList = ['tappay', 'linepay'];
        }

        $paymentList = array_reduce($availablePaymentList, function (array $list, string $paymentType) {
            $list[$paymentType] = $this->getPaymentData($paymentType);
            return $list;
        }, []);

        Log::info('payment_list', $paymentList);



//        $paymentList = [
//            'credit-card' => [
//                'name' => 'Credit Card',
//                'data' => $this->getPaymentData('tappay'),
//            ],
//            'line-pay' =>  [
//                'name' => 'Line Pay',
//                'data' => $this->getPaymentData('linepay'),
//            ],
//        ];

        return view('payment', [
            'langCode' => $langCode,
            'currencyCode' => $currencyCode,
            'paymentList' => $paymentList,
        ]);
    }

    public function result() {
        return view('payment_result');
    }

    private function encryptBody(string $payload)
    {
        $key = config('aes.jsondata_key');
        return GibberishAES::enc($payload, $key);
    }

    private function getPaymentData(string $paymentMethod)
    {
        $source_ref_no = strtoupper(Str::random(15));
        $pmch_value = config('payment_channel.' . $paymentMethod);

        $json_body = [
            'is_3d' => Arr::get($pmch_value, 'is_3d', true),
            'pmch_oid' => Arr::get($pmch_value, 'pmch_oid', 1),
            'pay_currency' => Arr::get($pmch_value, 'currency', 'TWD'),
            'pay_amount' => Arr::get($pmch_value, 'amount', 5),

            // TODO: change here
//            'return_url' => route('payment.result', ['pmch' => $paymentMethod]),
            'return_url' => route('payment.result'),
            'cancel_url' => 'https://google.com',
//            'cancel_url' => route('payment.cancel', ['pmch' => $paymentMethod]),

            'payment_source_info' => [
                'source_type' => 'KKDAY',
                'source_ref_no' => $source_ref_no,
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
            'json' => $json_body + Arr::get($pmch_value, 'custom_params', []),
        ];


        $encodedJsonData = $this->encryptBody(json_encode($jsondata));

        return [
            'name' => data_get($pmch_value, 'name', 'Unknown'),
            'data' => [
                'actionUrl' => config('url.kkday_payment_url') . Arr::get($pmch_value, 'url'),
                'body' => $encodedJsonData
            ],
        ];
    }
}
