<?php

namespace TFS\Mpesa\Services;

use TFS\Mpesa\MpesaClient;
use TFS\Mpesa\Support\NormalizesParameters;
use TFS\Mpesa\Exceptions\ValidationException;

class BalanceService
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
            'partyA' => 'party_a',
            'identifierType' => 'identifier_type',
        ];
    }

    /**
     * Check account balance
     *
     * @param array|string $params Array of parameters or partyA (shortcode)
     * @param string|null $remarks
     * @param int|null $identifierType 1=MSISDN, 2=Till, 4=Shortcode
     * @param string|null $callback
     * @return array
     *
     * Usage (PHP 7.4 - Array):
     *   $service->check([
     *       'party_a' => '600000',
     *       'remarks' => 'Balance inquiry',
     *       'identifier_type' => 4,    // optional, default: 4
     *       'callback' => 'https://...', // optional
     *   ]);
     *
     * Usage (PHP 8.x - Named Arguments):
     *   $service->check(
     *       partyA: '600000',
     *       remarks: 'Balance inquiry',
     *       identifierType: 4
     *   );
     *
     * Usage (Positional - Both versions):
     *   $service->check('600000', 'Balance inquiry', 4);
     */
    public function check(
        $params,
        ?string $remarks = null,
        ?int $identifierType = null,
        ?string $callback = null
    ): array {
        // Normalize parameters
        $data = $this->normalizeParams($params, [
            'remarks' => $remarks,
            'identifier_type' => $identifierType,
            'callback' => $callback,
        ], 'party_a');

        // Set defaults
        $identifierType = $data['identifier_type'] ?? 4;

        $this->validateIdentifierType($identifierType);

        if (empty($data['party_a'])) {
            throw new ValidationException('PartyA (shortcode) is required');
        }

        if (empty($data['remarks'])) {
            throw new ValidationException('Remarks is required');
        }

        $callback = $data['callback'] ?? config('mpesa.balance_callback_url');
        $url = $this->client->getConfig('balance_url');

        $payload = [
            'CommandID' => 'AccountBalance',
            'PartyA' => $data['party_a'],
            'IdentifierType' => $identifierType,
            'Remarks' => $data['remarks'],
            'Initiator' => $this->client->getConfig('initiator_name'),
            'SecurityCredential' => $this->client->generateSecurityCredential(),
            'QueueTimeOutURL' => "{$callback}/timeout",
            'ResultURL' => $callback,
        ];

        return $this->client->post($url, $payload, 'b2c');
    }

    /**
     * Validate identifier type
     */
    protected function validateIdentifierType(int $type): void
    {
        if (!in_array($type, [1, 2, 4], true)) {
            throw new ValidationException(
                'IdentifierType must be 1 (MSISDN), 2 (Till Number), or 4 (Organization shortcode)'
            );
        }
    }
}