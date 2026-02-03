<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'tokbox' => [
        'api_key' => env('TOKBOX_API_KEY', '47723411'),
        'api_secret' => env('TOKBOX_API_SECRET', '95d722d4d34dd4d46259a1d5837a18d07bc3b9d8'),
    ],

    'opentok' => [
        'node_server_url' => env('OPENTOK_NODE_SERVER_URL', 'https://localhost:4722'),
        'api_key' => env('OPENTOK_API_KEY'),
        'api_secret' => env('OPENTOK_API_SECRET'),
    ],
    'tabby' => [
        'api_base_url' => env('TABBY_API_BASE_URL', 'https://api.tabby.ai'),
        'api_key' => env('TABBY_API_KEY'),
        'merchant_code' => env('TABBY_MERCHANT_CODE'),
    ],

];
