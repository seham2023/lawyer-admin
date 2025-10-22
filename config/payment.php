<?php

return [
    'default' => env('PAYMENT_DEFAULT_PROVIDER', 'tabby'),

    'providers' => [
        'tabby' => [
            'api_base_url' => env('TABBYY_API_BASE_URL', 'https://api.tabby.ai'),
            'api_key' => env('TABBYY_API_KEY'),
            'merchant_code' => env('TABBYY_MERCHANT_CODE'),
        ],
        // Add other payment providers here in the future
    ],
];
