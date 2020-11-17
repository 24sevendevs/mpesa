<?php

return [
    'mode' => env('MPESA_MODE', 'live'), // Can only be 'sandbox' Or 'live'. If empty, 'live' will be used.
    'callback_url' => env('MPESA_CALLBACK_URL', 'https://c0da8d587e6d.ngrok.io/api/promotions/handle-result'),
    'sandbox' => [
        'consumer_key' => env('MPESA_SANDBOX_CONSUMER_KEY', 'jIJBAV99OQsznKIdjHGrM2NeyN7JC8BR'),
        'consumer_secret' => env('MPESA_SANDBOX_CONSUMER_SECRET', 'qoCqvvcE9qHIrOH3'),
        'b2c_consumer_key' => env('MPESA_SANDBOX_B2C_CONSUMER_KEY', 'jIJBAV99OQsznKIdjHGrM2NeyN7JC8BR'),
        'b2c_consumer_secret' => env('MPESA_SANDBOX_B2C_CONSUMER_SECRET', 'qoCqvvcE9qHIrOH3'),
        'b2c_shortcode' => env('MPESA_SANDBOX_B2C_SHORTCODE', '603003'),
        'token_url' => env('MPESA_SANDBOX_TOKEN_URL', 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials'),
        'stkpush_url' => env('MPESA_SANDBOX_STKPUSH_URL', 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest'),
        'stkquery_url' => env('MPESA_SANDBOX_STKPUSHQUERY_URL', 'https://sandbox.safaricom.co.ke/mpesa/stkpushquery/v1/query'),
        'b2c_url' => env('MPESA_SANDBOX_B2C_URL', 'https://sandbox.safaricom.co.ke/mpesa/b2c/v1/paymentrequest'),
        'passkey' => env('MPESA_SANDBOX_PASSKEY', 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919'),
        'shortcode' => env('MPESA_SANDBOX_SHORTCODE', '174379'),
        'initiator_name' => env('MPESA_SANDBOX_INITIATOR_NAME', 'apiop46'),
        'initiator_password' => env('MPESA_SANDBOX_INITIATOR_PASSWORD', 'Safaricom3003#')
    ],
    'live' => [
        'consumer_key' => env('MPESA_CONSUMER_KEY', ''),
        'consumer_secret' => env('MPESA_CONSUMER_SECRET', ''),
        'b2c_consumer_key' => env('MPESA_B2C_CONSUMER_KEY', ''),
        'b2c_consumer_secret' => env('MPESA_B2C_CONSUMER_SECRET', ''),
        'b2c_shortcode' => env('MPESA_B2C_SHORTCODE', ''),
        'token_url' => env('MPESA_TOKEN_URL', 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials'),
        'stkpush_url' => env('MPESA_STKPUSH_URL', 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest'),
        'stkquery_url' => env('MPESA_STKPUSHQUERY_URL', 'https://api.safaricom.co.ke/mpesa/stkpushquery/v1/query'),
        'b2c_url' => env('MPESA_B2C_URL', 'https://api.safaricom.co.ke/mpesa/b2c/v1/paymentrequest'),
        'passkey' => env('MPESA_PASSKEY', ''),
        'shortcode' => env('MPESA_SHORTCODE', ''),
        'initiator_name' => env('MPESA_INITIATOR_NAME', ''),
        'initiator_password' => env('MPESA_INITIATOR_PASSWORD', '')
    ],
    'settings' => array(
        'mode' => env('MPESA_EXPRESS', ''),
        'Http.ConnectionTimeOut' => 30,
        'log.LogEnabled' => true,
        'log.FileName' => storage_path() . '/logs/mpesa.log',
        'log.LogLevel' => 'ERROR',
    ),
];
