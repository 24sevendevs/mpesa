<?php

namespace TFS\Mpesa;

use Zttp\Zttp;

use function GuzzleHttp\json_decode;

// use function GuzzleHttp\json_decode;

class Mpesa
{
    public static function get_access_token($type = "c2b")
    {
        if ($type == "b2c") {
            $consumer_key = \Config::get("mpesa." . config('mpesa.mode') . ".b2c_consumer_key");
            $consumer_secret = \Config::get("mpesa." . config('mpesa.mode') . ".b2c_consumer_secret");
        } else {
            $consumer_key = \Config::get("mpesa." . config('mpesa.mode') . ".consumer_key");
            $consumer_secret = \Config::get("mpesa." . config('mpesa.mode') . ".consumer_secret");
        }

        $token_url = \Config::get("mpesa." . config('mpesa.mode') . ".token_url");
        $response = Zttp::withBasicAuth($consumer_key, $consumer_secret)->get($token_url);

        $access_token = json_decode($response, true)['access_token'];

        return $access_token;
    }

    public static function mpesa_express($phone, $amount, $AccountReference, $TransactionDesc, $callback = null)
    {
        if ($phone == "" || $phone == null || $amount == "" || $amount == null || $AccountReference == "" || $AccountReference == null || $TransactionDesc == "" || $TransactionDesc == null) {
            return response()->json([
                "error" => "invalid data. All parameters must not be null"
            ], 403);
        }
        if (!$callback) {
            $callback = config('mpesa.callback_url');
        }
        $access_token = Mpesa::get_access_token();

        $time = date('YmdHis');
        $shortcode = \Config::get("mpesa." . config('mpesa.mode') . ".shortcode");
        $passkey = \Config::get("mpesa." . config('mpesa.mode') . ".passkey");
        $password = base64_encode($shortcode . $passkey . $time);

        $stkpush_url = \Config::get("mpesa." . config('mpesa.mode') . ".stkpush_url");

        $headers = [
            'Authorization' => 'Bearer ' . $access_token,
            'Content-Type' => 'application/json',
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
            "CallBackURL" => $callback,
            "QueueTimeOutURL" => $callback,
            "AccountReference" => $AccountReference,
            "TransactionDesc" => $TransactionDesc
        ];

        $response = Zttp::withHeaders($headers)->post($stkpush_url, $data);


        return json_decode($response, true);
    }

    public static function b2c($phone, $amount, $occassion, $remarks, $callback = null, $command_id = null)
    {
        if ($phone == "" || $phone == null || $amount == "" || $amount == null || $occassion == "" || $occassion == null || $remarks == "" || $remarks == null) {
            return response()->json([
                "error" => "Invalid data. All parameters must not be null"
            ], 403);
        }
        if (!$callback) {
            $callback = config('mpesa.callback_url');
        }
        if (!$command_id) {
            $command_id = "BusinessPayment";
        } elseif ($command_id != "SalaryPayment" && $command_id != "BusinessPayment" && $command_id != "PromotionPayment") {
            return response()->json([
                "error" => "invalid data. Command ID can only be SalaryPayment, BusinessPayment or PromotionPayment"
            ], 403);
        }
        $access_token = Mpesa::get_access_token("b2c");

        $initiator_name = \Config::get("mpesa." . config('mpesa.mode') . ".initiator_name");
        $initiator_password = \Config::get("mpesa." . config('mpesa.mode') . ".initiator_password");
        $shortcode = \Config::get("mpesa." . config('mpesa.mode') . ".b2c_shortcode");

        if (config('mpesa.mode') == "sandbox") {
            $security_credential = "XMiKlEz4iuquErci7bL3nF/T8Ej5NdrHB4aUvjczqkikaocdTnVw3s1mQlzhMNZqtRSqqEWrQAhQT3OwkiYfHKBf1YUnykxXUo6UO1eXM82+0k6ZEVb90JEAoTvCOK9JEOPEFusqMRtSrxca4gU3qEA0CyLpY3k7ZWLiNisuaWWL2zDJSlRBBz8bn4waOLuLLz3aB1NVQYaxtlLjf6ITah7q2nx2lt1NKCkCImg/e/rKfJTzrmgRHbV2+3MC4t4SKJRwMosHBXd0FjOzFY5IO1/b7EBbwcmMIZMsuyFhnlSvjqolllFc9SToK37h+G5TMhZthJBA3PfkAWyjJK6nqQ==";
        } else {
            //GENERATE SECURITY CREDENTIAL USING THE $publicKey
            $publicKey = file_get_contents(__DIR__ . "/assets/public_keycert.cer");
            openssl_public_encrypt($initiator_password, $encrypted, $publicKey, OPENSSL_PKCS1_PADDING);
            $security_credential = base64_encode($encrypted);
        }
        $b2c_url = \Config::get("mpesa." . config('mpesa.mode') . ".b2c_url");

        $headers = [
            'Authorization' => 'Bearer ' . $access_token,
            'Content-Type' => 'application/json',
        ];

        $data = [
            "InitiatorName" => $initiator_name,
            "SecurityCredential" => $security_credential,
            "CommandID" => $command_id,
            "Amount" => $amount,
            "PartyA" => $shortcode,
            "PartyB" => $phone,
            "ResultURL" => $callback,
            "QueueTimeOutURL" => $callback . "/timeout",
            "Remarks" => $remarks,
            "Occassion" => $occassion
        ];

        // dd($initiator_password, $data);

        $response = Zttp::withHeaders($headers)->post($b2c_url, $data);

        return json_decode($response, true);
    }

    public static function query_request($CheckoutRequestID)
    {
        $access_token = Mpesa::get_access_token();
        $time = date('YmdHis');
        $shortcode = \Config::get("mpesa." . config('mpesa.mode') . ".shortcode");
        $passkey = \Config::get("mpesa." . config('mpesa.mode') . ".passkey");
        $password = base64_encode($shortcode . $passkey . $time);

        $query_url = \Config::get("mpesa." . config('mpesa.mode') . ".stkquery_url");
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
