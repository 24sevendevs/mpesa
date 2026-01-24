<?php

namespace TFS\Mpesa\Services;

use TFS\Mpesa\MpesaClient;
use TFS\Mpesa\Support\Validator;
use TFS\Mpesa\Support\NormalizesParameters;
use TFS\Mpesa\Exceptions\ValidationException;

class STKPushService
{
    use NormalizesParameters;

    protected MpesaClient $client;

    public function __construct(MpesaClient $client)
    {
        $this->client = $client;
    }

    /**
     * Key mapping from camelCase to snake_case
     */
    protected function getKeyMap(): array
    {
        return [
            'accountReference' => 'account_reference',
            'transactionDesc' => 'transaction_desc',
            'checkoutRequestId' => 'checkout_request_id',
        ];
    }

    /**
     * Initiate STK Push
     *
     * @param array|string $params Array of parameters or phone number
     * @param string|null $amount
     * @param string|null $accountReference
     * @param string|null $transactionDesc
     * @param string|null $callback
     * @return array
     */
    public function push(
        $params,
        ?string $amount = null,
        ?string $accountReference = null,
        ?string $transactionDesc = null,
        ?string $callback = null
    ): array {
        // Normalize parameters using trait
        $data = $this->normalizeParams($params, [
            'amount' => $amount,
            'account_reference' => $accountReference,
            'transaction_desc' => $transactionDesc,
            'callback' => $callback,
        ], 'phone');

        $this->validatePushParams($data);

        $callback = $this->getParam($data, 'callback', config('mpesa.callback_url'));
        $timestamp = now()->format('YmdHis');
        $shortcode = $this->client->getConfig('shortcode');
        $passkey = $this->client->getConfig('passkey');
        $password = base64_encode($shortcode . $passkey . $timestamp);
        $url = $this->client->getConfig('stkpush_url');

        $payload = [
            'BusinessShortCode' => $shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => $data['amount'],
            'PartyA' => $data['phone'],
            'PartyB' => $shortcode,
            'PhoneNumber' => $data['phone'],
            'CallBackURL' => $callback,
            'AccountReference' => $data['account_reference'],
            'TransactionDesc' => $data['transaction_desc'],
        ];

        return $this->client->post($url, $payload);
    }

    /**
     * Query STK Push status
     *
     * @param array|string $params Array of parameters or checkout request ID
     * @return array
     */
    public function query($params): array
    {
        $data = $this->normalizeParams($params, [], 'checkout_request_id');

        $checkoutRequestId = $this->getParam($data, 'checkout_request_id');

        if (empty($checkoutRequestId)) {
            throw new ValidationException('CheckoutRequestID is required');
        }

        $timestamp = now()->format('YmdHis');
        $shortcode = $this->client->getConfig('shortcode');
        $passkey = $this->client->getConfig('passkey');
        $password = base64_encode($shortcode . $passkey . $timestamp);
        $url = $this->client->getConfig('stkquery_url');

        $payload = [
            'BusinessShortCode' => $shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'CheckoutRequestID' => $checkoutRequestId,
        ];

        return $this->client->post($url, $payload);
    }

    /**
     * Validate STK Push parameters
     */
    protected function validatePushParams(array $data): void
    {
        $errors = [];

        if (!isset($data['phone']) || !Validator::isValidPhone($data['phone'])) {
            $errors[] = 'Phone number must be valid (254XXXXXXXXX format)';
        }

        if (!isset($data['amount']) || !Validator::isValidAmount($data['amount'])) {
            $errors[] = 'Amount must be a positive number';
        }

        if (empty($data['account_reference'])) {
            $errors[] = 'AccountReference is required';
        }

        if (empty($data['transaction_desc'])) {
            $errors[] = 'TransactionDesc is required';
        }

        if (!empty($errors)) {
            throw new ValidationException(implode(', ', $errors));
        }
    }
}