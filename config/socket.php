<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Socket.IO Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Socket.IO server connection
    |
    */

    'url' => env('SOCKET_IO_URL', 'https://qestass.com:4722'),
    'path' => env('SOCKET_IO_PATH', '/socket.io'),

    /*
    |--------------------------------------------------------------------------
    | Vonage TokBox Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Vonage TokBox video/audio calls
    |
    */

    'vonage' => [
        'api_key' => env('VONAGE_API_KEY', '47723411'),
        'api_secret' => env('VONAGE_API_SECRET', '95d722d4d34dd4d46259a1d5837a18d07bc3b9d8'),
    ],
];
