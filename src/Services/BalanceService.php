<?php

namespace TFS\Mpesa\Services;

use TFS\Mpesa\MpesaClient;
use TFS\Mpesa\Exceptions\ValidationException;

class BalanceService
{
    protected MpesaClient $client;

    public function __construct(MpesaClient $client)
    {
        $this->client = $client;
    }

    public function check(
        string $partyA,
        string $remarks,
        int $identifierType = 4,
        ?string $callback = null
    ): array {
        $this->validateIdentifierType($identifierType);

        $callback ??= config('mpesa.balance_callback_url');
        $url = $this->client->getConfig('balance_url');

        $data = [
            'CommandID' => 'AccountBalance',
            'PartyA' => $partyA,
            'IdentifierType' => $identifierType,
            'Remarks' => $remarks,
            'Initiator' => $this->client->getConfig('initiator_name'),
            'SecurityCredential' => $this->client->generateSecurityCredential(),
            'QueueTimeOutURL' => "{$callback}/timeout",
            'ResultURL' => $callback,
        ];

        return $this->client->post($url, $data, 'b2c');
    }

    protected function validateIdentifierType(int $type): void
    {
        if (!in_array($type, [1, 2, 4], true)) {
            throw new ValidationException(
                'IdentifierType must be 1 (MSISDN), 2 (Till Number), or 4 (Organization shortcode)'
            );
        }
    }
}