<?php

namespace TFS\Mpesa\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array stkPush(string $phone, string $amount, string $accountReference, string $transactionDesc, ?string $callback = null, ?array $auth = null)
 * @method static array stkQuery(string $checkoutRequestId, ?array $auth = null)
 * @method static array b2c(string $phone, string $amount, string $occasion, string $remarks, ?string $callback = null, ?string $commandId = null, ?array $auth = null)
 * @method static array b2bPayBill(string $partyB, string $amount, string $accountReference, string $remarks, ?string $requester = null, ?string $callback = null, ?array $auth = null)
 * @method static array b2bBuyGoods(string $partyB, string $amount, string $accountReference, string $remarks, ?string $requester = null, ?string $callback = null, ?array $auth = null)
 * @method static array b2Pochi(string $partyB, string $amount, string $occasion, string $remarks, string $originatorConversationId, ?string $callback = null, ?array $auth = null)
 * @method static array balance(string $partyA, string $remarks, int $identifierType = 4, ?string $callback = null, ?array $auth = null)
 * @method static array registerC2BUrl(string $validationUrl, string $confirmationUrl, string $responseType = 'Completed', ?string $shortCode = null, ?array $auth = null)
 * @method static array mpesa_express(...$args) @deprecated Use stkPush() instead
 *
 * @see \TFS\Mpesa\Mpesa
 */
class Mpesa extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'mpesa';
    }
}
