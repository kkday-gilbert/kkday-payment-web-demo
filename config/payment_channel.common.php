<?php

return  [
    'pay_amount' => 1,
    'payment_source_info' => [
        'source_type' => 'KKDAY',
        'source_ref_no' => '',
        'source_code' => 'WEB',
    ],
    'payer_info' => [
        'first_name' => 'Su',
        'last_name' => 'Iris',
        'phone' => '0912345678',
        'email' => 'susu.su@kkday.com',
    ],
    'member' => [
        'member_uuid' => '',
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
