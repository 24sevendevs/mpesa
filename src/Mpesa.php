<?php

namespace TFS\Mpesa;

use Zttp\Zttp;

use function GuzzleHttp\json_decode;

// use function GuzzleHttp\json_decode;

class Mpesa
{
    public static function get_access_token()
    {
        $token_url = \Config::get("mpesa." . config('mpesa.mode') . ".token_url");
        $consumer_key = \Config::get("mpesa." . config('mpesa.mode') . ".consumer_key");
        $consumer_secret = \Config::get("mpesa." . config('mpesa.mode') . ".consumer_secret");

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

    public static function c2b($phone, $amount, $occassion, $remarks, $callback = null, $command_id = null)
    {
        if ($phone == "" || $phone == null || $amount == "" || $amount == null || $occassion == "" || $occassion == null || $remarks == "" || $remarks == null) {
            return response()->json([
                "error" => "invalid data. All parameters must not be null"
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
        $access_token = Mpesa::get_access_token();

        $initiator_name = \Config::get("mpesa." . config('mpesa.mode') . ".initiator_name");
        $initiator_password = \Config::get("mpesa." . config('mpesa.mode') . ".initiator_password");
        $shortcode = \Config::get("mpesa." . config('mpesa.mode') . ".shortcode");

        $publicKey = file_get_contents(__DIR__ . "/assets/public_keycert.cer");
        openssl_public_encrypt($initiator_password, $encrypted, $publicKey, OPENSSL_PKCS1_PADDING);
        //GENERATE SECURITY CREDENTIAL USING THE $publicKey
        $security_credential = base64_encode($encrypted);

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
