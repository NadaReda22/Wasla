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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
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

     'green_api' => [
        'host' => env('GREEN_API_HOST', 'https://api.greenapi.com'),
        'instance_id' => env('GREEN_API_INSTANCE_ID'),
        'token' => env('GREEN_API_TOKEN'),
    ],

    'kashier' => [
    'base_url' => env('KASHIER_BASE_URL'),
    'merchant_id' => env('KASHIER_MERCHANT_ID'),
    'api_key' => env('KASHIER_API_KEY'),
    'webhook_secret' => env('KASHIER_WEBHOOK_SECRET'),
],

];
