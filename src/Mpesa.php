<?php

namespace TFS\Mpesa;
use Zttp\Zttp;

// use function GuzzleHttp\json_decode;

class Mpesa
{
    public static function get_access_token()
    {
        $token_url = \Config::get("mpesa.".config('mpesa.mode').".token_url");
        $consumer_key = \Config::get("mpesa.".config('mpesa.mode').".consumer_key");
        $consumer_secret = \Config::get("mpesa.".config('mpesa.mode').".consumer_secret");


        $response = Zttp::withBasicAuth($consumer_key, $consumer_secret)->get($token_url);

        $access_token = json_decode($response, true)['access_token'];

        return $access_token;

    }

    public static function mpesa_express($phone, $amount, $AccountReference, $TransactionDesc)
    {
        if($phone == "" || $phone == null || $amount == "" || $amount == null || $AccountReference == "" || $AccountReference == null || $TransactionDesc == "" || $TransactionDesc == null){
            return response()->json([
                "error" => "invalid data. All parameters must not be null"
            ], 403);
        }
        $access_token = Mpesa::get_access_token();

        $time = date('YmdHis');
        $shortcode = \Config::get("mpesa.".config('mpesa.mode').".shortcode");
        $passkey = \Config::get("mpesa.".config('mpesa.mode').".passkey");
        $password = base64_encode($shortcode . $passkey . $time);

        $stkpush_url = \Config::get("mpesa.".config('mpesa.mode').".stkpush_url");

        $headers = [
            'Authorization' => 'Bearer ' . $access_token,
            'Content-Type' => 'application/json',
            // 'Host: sandbox.safaricom.co.ke'
        ];
        $data = [
            "BusinessShortCode" => $shortcode,
            "Password" => $password,
            "Timestamp" => $time,
            "TransactionType" => "CustomerPayBillOnline",
            "Amount" => $amount,
            "PartyA" => $phone,
            "PartyB" => $shortcode,
            "PhoneNumber" => $phone,
            "CallBackURL" => config('mpesa.callback_url'),
            "QueueTimeOutURL" => config('mpesa.callback_url'),
            "AccountReference" => $AccountReference,
            "TransactionDesc" => $TransactionDesc
        ];

        $response = Zttp::withHeaders($headers)->post($stkpush_url, $data);


        return json_decode($response, true);
    }

    public static function query_request($CheckoutRequestID)
    {
        $access_token = Mpesa::get_access_token();
        $time = date('YmdHis');
        $shortcode = \Config::get("mpesa.".config('mpesa.mode').".shortcode");
        $passkey = \Config::get("mpesa.".config('mpesa.mode').".passkey");
        $password = base64_encode($shortcode . $passkey . $time);

        $query_url = \Config::get("mpesa.".config('mpesa.mode').".stkquery_url");
        $headers = [
            'Authorization' => 'Bearer ' . $access_token,
            'Content-Type' => 'application/json',
        ];
        $data = [
            "BusinessShortCode" => $shortcode,
            "Password" => $password,
            "Timestamp" => $time,
            "CheckoutRequestID" => $CheckoutRequestID,
        ];
        $response = Zttp::withHeaders($headers)->post($query_url, $data);
        return json_decode($response, true);
    }
}
