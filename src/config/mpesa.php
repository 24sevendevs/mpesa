<?php

return [
    'mode' => env('MPESA_MODE', 'live'),
    'callback_url' => env('MPESA_CALLBACK_URL'),
    'balance_callback_url' => env('MPESA_BALANCE_CALLBACK_URL'),
    
    'sandbox' => [
        'consumer_key' => env('MPESA_SANDBOX_CONSUMER_KEY'),
        'consumer_secret' => env('MPESA_SANDBOX_CONSUMER_SECRET'),
        'b2c_consumer_key' => env('MPESA_SANDBOX_B2C_CONSUMER_KEY'),
        'b2c_consumer_secret' => env('MPESA_SANDBOX_B2C_CONSUMER_SECRET'),
        'b2c_shortcode' => env('MPESA_SANDBOX_B2C_SHORTCODE', '600000'),
        'shortcode' => env('MPESA_SANDBOX_SHORTCODE', '174379'),
        'passkey' => env('MPESA_SANDBOX_PASSKEY'),
        'initiator_name' => env('MPESA_SANDBOX_INITIATOR_NAME', 'testapi'),
        'initiator_password' => env('MPESA_SANDBOX_INITIATOR_PASSWORD', 'Safaricom999!*!'),
        
        'token_url' => 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials',
        'stkpush_url' => 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest',
        'stkquery_url' => 'https://sandbox.safaricom.co.ke/mpesa/stkpushquery/v1/query',
        'balance_url' => 'https://sandbox.safaricom.co.ke/mpesa/accountbalance/v1/query',
        'b2c_url' => 'https://sandbox.safaricom.co.ke/mpesa/b2c/v1/paymentrequest',
        'b2b_url' => 'https://sandbox.safaricom.co.ke/mpesa/b2b/v1/paymentrequest',
        'b2pochi_url' => 'https://sandbox.safaricom.co.ke/mpesa/b2pochi/v1/paymentrequest',
        'c2b_register_url' => 'https://sandbox.safaricom.co.ke/mpesa/c2b/v1/registerurl',
    ],
    
    'live' => [
        'consumer_key' => env('MPESA_CONSUMER_KEY'),
        'consumer_secret' => env('MPESA_CONSUMER_SECRET'),
        'b2c_consumer_key' => env('MPESA_B2C_CONSUMER_KEY'),
        'b2c_consumer_secret' => env('MPESA_B2C_CONSUMER_SECRET'),
        'b2c_shortcode' => env('MPESA_B2C_SHORTCODE'),
        'shortcode' => env('MPESA_SHORTCODE'),
        'passkey' => env('MPESA_PASSKEY'),
        'initiator_name' => env('MPESA_INITIATOR_NAME'),
        'initiator_password' => env('MPESA_INITIATOR_PASSWORD'),
        
        'token_url' => 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials',
        'stkpush_url' => 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest',
        'stkquery_url' => 'https://api.safaricom.co.ke/mpesa/stkpushquery/v1/query',
        'balance_url' => 'https://api.safaricom.co.ke/mpesa/accountbalance/v1/query',
        'b2c_url' => 'https://api.safaricom.co.ke/mpesa/b2c/v1/paymentrequest',
        'b2b_url' => 'https://api.safaricom.co.ke/mpesa/b2b/v1/paymentrequest',
        'b2pochi_url' => 'https://api.safaricom.co.ke/mpesa/b2pochi/v1/paymentrequest',
        'c2b_register_url' => 'https://api.safaricom.co.ke/mpesa/c2b/v2/registerurl',
    ],
];