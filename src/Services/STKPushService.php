<?php

namespace TFS\Mpesa\Services;

use TFS\Mpesa\MpesaClient;
use TFS\Mpesa\Support\Validator;
use TFS\Mpesa\Exceptions\ValidationException;

class STKPushService
{
    protected MpesaClient $client;

    public function __construct(MpesaClient $client)
    {
        $this->client = $client;
    }

    /**
     * Initiate STK Push
     */
    public function push(
        string $phone,
        string $amount,
        string $accountReference,
        string $transactionDesc,
        ?string $callback = null
    ): array {
        $this->validateParams($phone, $amount, $accountReference, $transactionDesc);

        $callback ??= config('mpesa.callback_url');
        $timestamp = now()->format('YmdHis');
        $shortcode = $this->client->getConfig('shortcode');
        $passkey = $this->client->getConfig('passkey');
        $password = base64_encode($shortcode . $passkey . $timestamp);
        $url = $this->client->getConfig('stkpush_url');

        $data = [
            'BusinessShortCode' => $shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => $amount,
            'PartyA' => $phone,
            'PartyB' => $shortcode,
            'PhoneNumber' => $phone,
            'CallBackURL' => $callback,
            'AccountReference' => $accountReference,
            'TransactionDesc' => $transactionDesc,
        ];

        return $this->client->post($url, $data);
    }

    /**
     * Query STK Push status
     */
    public function query(string $checkoutRequestId): array
    {
        if (empty($checkoutRequestId)) {
            throw new ValidationException('CheckoutRequestID is required');
        }

        $timestamp = now()->format('YmdHis');
        $shortcode = $this->client->getConfig('shortcode');
        $passkey = $this->client->getConfig('passkey');
        $password = base64_encode($shortcode . $passkey . $timestamp);
        $url = $this->client->getConfig('stkquery_url');

        $data = [
            'BusinessShortCode' => $shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'CheckoutRequestID' => $checkoutRequestId,
        ];

        return $this->client->post($url, $data);
    }

    /**
     * Validate parameters
     */
    protected function validateParams(
        string $phone,
        string $amount,
        string $accountReference,
        string $transactionDesc
    ): void {
        $errors = [];

        if (!Validator::isValidPhone($phone)) {
            $errors[] = 'Phone number must be valid (254XXXXXXXXX format)';
        }

        if (!Validator::isValidAmount($amount)) {
            $errors[] = 'Amount must be a positive number';
        }

        if (empty($accountReference)) {
            $errors[] = 'AccountReference is required';
        }

        if (empty($transactionDesc)) {
            $errors[] = 'TransactionDesc is required';
        }

        if (!empty($errors)) {
            throw new ValidationException(implode(', ', $errors));
        }
    }
}