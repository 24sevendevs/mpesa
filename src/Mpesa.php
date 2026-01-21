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
     */
    public static function stkPush(
        string $phone,
        string $amount,
        string $accountReference,
        string $transactionDesc,
        ?string $callback = null,
        ?array $auth = null
    ): array {
        return (new STKPushService(new MpesaClient($auth)))->push(
            phone: $phone,
            amount: $amount,
            accountReference: $accountReference,
            transactionDesc: $transactionDesc,
            callback: $callback
        );
    }

    /**
     * Query STK Push status
     */
    public static function stkQuery(string $checkoutRequestId, ?array $auth = null): array
    {
        return (new STKPushService(new MpesaClient($auth)))->query($checkoutRequestId);
    }

    /**
     * Business to Customer (B2C) payment
     */
    public static function b2c(
        string $phone,
        string $amount,
        string $occasion,
        string $remarks,
        ?string $callback = null,
        ?string $commandId = null,
        ?array $auth = null
    ): array {
        return (new B2CService(new MpesaClient($auth)))->pay(
            phone: $phone,
            amount: $amount,
            occasion: $occasion,
            remarks: $remarks,
            callback: $callback,
            commandId: $commandId
        );
    }

    /**
     * Business to Business PayBill payment
     */
    public static function b2bPayBill(
        string $partyB,
        string $amount,
        string $accountReference,
        string $remarks,
        ?string $requester = null,
        ?string $callback = null,
        ?array $auth = null
    ): array {
        return (new B2BService(new MpesaClient($auth)))->payBill(
            partyB: $partyB,
            amount: $amount,
            accountReference: $accountReference,
            remarks: $remarks,
            requester: $requester,
            callback: $callback
        );
    }

    /**
     * Business to Business BuyGoods payment
     */
    public static function b2bBuyGoods(
        string $partyB,
        string $amount,
        string $accountReference,
        string $remarks,
        ?string $requester = null,
        ?string $callback = null,
        ?array $auth = null
    ): array {
        return (new B2BService(new MpesaClient($auth)))->buyGoods(
            partyB: $partyB,
            amount: $amount,
            accountReference: $accountReference,
            remarks: $remarks,
            requester: $requester,
            callback: $callback
        );
    }

    /**
     * Business to Pochi (Business Wallet) payment
     */
    public static function b2Pochi(
        string $partyB,
        string $amount,
        string $occasion,
        string $remarks,
        string $originatorConversationId,
        ?string $callback = null,
        ?array $auth = null
    ): array {
        return (new B2BService(new MpesaClient($auth)))->payToPochi(
            partyB: $partyB,
            amount: $amount,
            occasion: $occasion,
            remarks: $remarks,
            originatorConversationId: $originatorConversationId,
            callback: $callback
        );
    }

    /**
     * Check account balance
     */
    public static function balance(
        string $partyA,
        string $remarks,
        int $identifierType = 4,
        ?string $callback = null,
        ?array $auth = null
    ): array {
        return (new BalanceService(new MpesaClient($auth)))->check(
            partyA: $partyA,
            remarks: $remarks,
            identifierType: $identifierType,
            callback: $callback
        );
    }

    /**
     * Register C2B URLs
     */
    public static function registerC2BUrl(
        string $validationUrl,
        string $confirmationUrl,
        string $responseType = 'Completed',
        ?string $shortCode = null,
        ?array $auth = null
    ): array {
        return (new C2BService(new MpesaClient($auth)))->registerUrls(
            validationUrl: $validationUrl,
            confirmationUrl: $confirmationUrl,
            responseType: $responseType,
            shortCode: $shortCode
        );
    }

    /**
     * Legacy method name support
     * @deprecated Use stkPush() instead
     */
    public static function mpesa_express(...$args): array
    {
        return self::stkPush(...$args);
    }
}