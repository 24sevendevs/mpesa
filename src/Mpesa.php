<?php

namespace TFS\Mpesa;

use TFS\Mpesa\Services\{B2CService, B2BService, C2BService, STKPushService, BalanceService};
use TFS\Mpesa\Exceptions\MpesaException;

class Mpesa
{
    protected MpesaClient $client;

    public function __construct(?array $config = null)
    {
        $this->client = new MpesaClient($config);
    }

    /**
     * Create a new instance with custom configuration
     */
    public static function withConfig(array $config): self
    {
        return new self($config);
    }

    /**
     * STK Push / M-Pesa Express
     *
     * Usage (PHP 7.4 - Array):
     *   Mpesa::stkPush([
     *       'phone' => '254712345678',
     *       'amount' => '100',
     *       'account_reference' => 'INV-123',
     *       'transaction_desc' => 'Payment',
     *   ]);
     *
     * Usage (PHP 8.x - Named Arguments):
     *   Mpesa::stkPush(
     *       phone: '254712345678',
     *       amount: '100',
     *       accountReference: 'INV-123',
     *       transactionDesc: 'Payment'
     *   );
     *
     * @param array|string $params Array of params or phone number
     * @param string|null $amount
     * @param string|null $accountReference
     * @param string|null $transactionDesc
     * @param string|null $callback
     * @param array|null $auth Custom authentication credentials
     * @return array
     */
    public static function stkPush(
        $params,
        ?string $amount = null,
        ?string $accountReference = null,
        ?string $transactionDesc = null,
        ?string $callback = null,
        ?array $auth = null
    ): array {
        // Extract auth from array params if present
        if (is_array($params) && isset($params['auth'])) {
            $auth = $params['auth'];
            unset($params['auth']);
        }

        return (new STKPushService(new MpesaClient($auth)))->push(
            $params, $amount, $accountReference, $transactionDesc, $callback
        );
    }

    /**
     * Query STK Push status
     *
     * Usage (PHP 7.4 - Array):
     *   Mpesa::stkQuery(['checkout_request_id' => 'ws_CO_123...']);
     *
     * Usage (PHP 8.x - Named Arguments):
     *   Mpesa::stkQuery(checkoutRequestId: 'ws_CO_123...');
     *
     * Usage (Positional - Both):
     *   Mpesa::stkQuery('ws_CO_123...');
     *
     * @param array|string $params Array of params or checkout request ID
     * @param array|null $auth
     * @return array
     */
    public static function stkQuery($params, ?array $auth = null): array
    {
        if (is_array($params) && isset($params['auth'])) {
            $auth = $params['auth'];
            unset($params['auth']);
        }

        return (new STKPushService(new MpesaClient($auth)))->query($params);
    }

    /**
     * Business to Customer (B2C) payment
     *
     * Usage (PHP 7.4 - Array):
     *   Mpesa::b2c([
     *       'phone' => '254712345678',
     *       'amount' => '1000',
     *       'occasion' => 'Salary',
     *       'remarks' => 'Monthly payment',
     *       'command_id' => 'SalaryPayment', // optional
     *   ]);
     *
     * Usage (PHP 8.x - Named Arguments):
     *   Mpesa::b2c(
     *       phone: '254712345678',
     *       amount: '1000',
     *       occasion: 'Salary',
     *       remarks: 'Monthly payment',
     *       commandId: 'SalaryPayment'
     *   );
     *
     * @param array|string $params
     * @param string|null $amount
     * @param string|null $occasion
     * @param string|null $remarks
     * @param string|null $callback
     * @param string|null $commandId
     * @param array|null $auth
     * @return array
     */
    public static function b2c(
        $params,
        ?string $amount = null,
        ?string $occasion = null,
        ?string $remarks = null,
        ?string $callback = null,
        ?string $commandId = null,
        ?array $auth = null
    ): array {
        if (is_array($params) && isset($params['auth'])) {
            $auth = $params['auth'];
            unset($params['auth']);
        }

        return (new B2CService(new MpesaClient($auth)))->pay(
            $params, $amount, $occasion, $remarks, $callback, $commandId
        );
    }

    /**
     * Business to Business PayBill payment
     *
     * Usage (PHP 7.4 - Array):
     *   Mpesa::b2bPayBill([
     *       'party_b' => '888880',
     *       'amount' => '1000',
     *       'account_reference' => 'ACC-123',
     *       'remarks' => 'Payment',
     *   ]);
     *
     * Usage (PHP 8.x - Named Arguments):
     *   Mpesa::b2bPayBill(
     *       partyB: '888880',
     *       amount: '1000',
     *       accountReference: 'ACC-123',
     *       remarks: 'Payment'
     *   );
     */
    public static function b2bPayBill(
        $params,
        ?string $amount = null,
        ?string $accountReference = null,
        ?string $remarks = null,
        ?string $requester = null,
        ?string $callback = null,
        ?array $auth = null
    ): array {
        if (is_array($params) && isset($params['auth'])) {
            $auth = $params['auth'];
            unset($params['auth']);
        }

        return (new B2BService(new MpesaClient($auth)))->payBill(
            $params, $amount, $accountReference, $remarks, $requester, $callback
        );
    }

    /**
     * Business to Business BuyGoods payment
     *
     * Usage (PHP 7.4 - Array):
     *   Mpesa::b2bBuyGoods([
     *       'party_b' => '888880',
     *       'amount' => '500',
     *       'account_reference' => 'ORDER-789',
     *       'remarks' => 'Payment',
     *   ]);
     *
     * Usage (PHP 8.x - Named Arguments):
     *   Mpesa::b2bBuyGoods(
     *       partyB: '888880',
     *       amount: '500',
     *       accountReference: 'ORDER-789',
     *       remarks: 'Payment'
     *   );
     */
    public static function b2bBuyGoods(
        $params,
        ?string $amount = null,
        ?string $accountReference = null,
        ?string $remarks = null,
        ?string $requester = null,
        ?string $callback = null,
        ?array $auth = null
    ): array {
        if (is_array($params) && isset($params['auth'])) {
            $auth = $params['auth'];
            unset($params['auth']);
        }

        return (new B2BService(new MpesaClient($auth)))->buyGoods(
            $params, $amount, $accountReference, $remarks, $requester, $callback
        );
    }

    /**
     * Business to Pochi (Business Wallet) payment
     *
     * Usage (PHP 7.4 - Array):
     *   Mpesa::b2Pochi([
     *       'party_b' => '254712345678',
     *       'amount' => '1000',
     *       'occasion' => 'Payment',
     *       'remarks' => 'Business payment',
     *       'originator_conversation_id' => 'CONV-123',
     *   ]);
     *
     * Usage (PHP 8.x - Named Arguments):
     *   Mpesa::b2Pochi(
     *       partyB: '254712345678',
     *       amount: '1000',
     *       occasion: 'Payment',
     *       remarks: 'Business payment',
     *       originatorConversationId: 'CONV-123'
     *   );
     */
    public static function b2Pochi(
        $params,
        ?string $amount = null,
        ?string $occasion = null,
        ?string $remarks = null,
        ?string $originatorConversationId = null,
        ?string $callback = null,
        ?array $auth = null
    ): array {
        if (is_array($params) && isset($params['auth'])) {
            $auth = $params['auth'];
            unset($params['auth']);
        }

        return (new B2BService(new MpesaClient($auth)))->payToPochi(
            $params, $amount, $occasion, $remarks, $originatorConversationId, $callback
        );
    }

    /**
     * Check account balance
     *
     * Usage (PHP 7.4 - Array):
     *   Mpesa::balance([
     *       'party_a' => '600000',
     *       'remarks' => 'Balance inquiry',
     *       'identifier_type' => 4,
     *   ]);
     *
     * Usage (PHP 8.x - Named Arguments):
     *   Mpesa::balance(
     *       partyA: '600000',
     *       remarks: 'Balance inquiry',
     *       identifierType: 4
     *   );
     */
    public static function balance(
        $params,
        ?string $remarks = null,
        ?int $identifierType = null,
        ?string $callback = null,
        ?array $auth = null
    ): array {
        if (is_array($params) && isset($params['auth'])) {
            $auth = $params['auth'];
            unset($params['auth']);
        }

        return (new BalanceService(new MpesaClient($auth)))->check(
            $params, $remarks, $identifierType, $callback
        );
    }

    /**
     * Register C2B URLs
     *
     * Usage (PHP 7.4 - Array):
     *   Mpesa::registerC2BUrl([
     *       'validation_url' => 'https://...',
     *       'confirmation_url' => 'https://...',
     *       'response_type' => 'Completed',
     *   ]);
     *
     * Usage (PHP 8.x - Named Arguments):
     *   Mpesa::registerC2BUrl(
     *       validationUrl: 'https://...',
     *       confirmationUrl: 'https://...',
     *       responseType: 'Completed'
     *   );
     */
    public static function registerC2BUrl(
        $params,
        ?string $confirmationUrl = null,
        ?string $responseType = null,
        ?string $shortCode = null,
        ?array $auth = null
    ): array {
        if (is_array($params) && isset($params['auth'])) {
            $auth = $params['auth'];
            unset($params['auth']);
        }

        return (new C2BService(new MpesaClient($auth)))->registerUrls(
            $params, $confirmationUrl, $responseType, $shortCode
        );
    }

    /**
     * Legacy method name support
     * @deprecated Use stkPush() instead
     */
    public static function mpesa_express($params, ...$args): array
    {
        return self::stkPush($params, ...$args);
    }
}