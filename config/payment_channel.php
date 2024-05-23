<?php

return [
    'linepay' => [
        'name' => 'LinePay',
        'pmch_oid' => 147,
        'currency' => 'TWD',
        'is_3d' => true,
        'url' => '/v1/channel/linepay/reserve',
        'amount' => 1,
    ],
    'travel_card' => [
        'name' => 'Tappay',
        'pmch_oid' => 248,
        'currency' => 'TWD',
        'is_3d' => false,
        'url' => '/v1/channel/tappay/auth',
        'custom_params' => [
            'payment_param1' => 'Bank SinoPac',
            'payment_param2' => [
                'prime' => 'test_3a2fb2b7e892b914a03c95dd4dd5dc7970c908df67a49527c0a648b2bc9',
            ],
            'credit_card_info' => [
                'issuer' => 'CTBC Bank',
                'card_type' => 'VISA',
                'card_country' => 'TAIWAN R.O.C.',
                'card_country_code' => 'TW',
                'first_six' => '123456',
                'last_four' => '7890',
            ],
        ],
    ],
];
