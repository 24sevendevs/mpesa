<?php

namespace TFS\Mpesa\Services;

use TFS\Mpesa\MpesaClient;
use TFS\Mpesa\Support\NormalizesParameters;
use TFS\Mpesa\Exceptions\ValidationException;

/**
 * Transaction Status Query — Safaricom's general-purpose reconciliation
 * API, covering C2B, B2B, B2C, Reversal, and IMT transactions. Distinct
 * from STKPushService::query(), which only checks STK Express prompts via
 * CheckoutRequestID.
 *
 * Field names here genuinely differ from the rest of this package's
 * conventions — verified against Safaricom's own documented request/
 * response/callback samples, not assumed from the B2C/Balance pattern:
 *   - `Initiator`, not `InitiatorName`
 *   - `OriginalConversationID` in the REQUEST (drops "-tor"); the
 *     response and callback both use the normal `OriginatorConversationID`
 *   - `Occasion`, not B2C/B2B's `Occassion`
 *
 * Async, same as every other Daraja command here: this call's response
 * only confirms the request was accepted. The actual status arrives later
 * via its own callback at ResultURL.
 */
class TransactionStatusService
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
            'transactionId' => 'transaction_id',
            'originalConversationId' => 'original_conversation_id',
            'identifierType' => 'identifier_type',
        ];
    }

    /**
     * Query the status of a transaction. Needs EITHER transactionId (the
     * M-Pesa receipt number) OR originalConversationId (the
     * OriginatorConversationID from the original request/response you're
     * checking on) — both may be supplied together if you have them.
     *
     * Usage (PHP 7.4 - Array):
     *   $service->query([
     *       'party_a' => '600782',
     *       'original_conversation_id' => 'WD123-abcdEFGH', // your own generated ID from initiation
     *       'remarks' => 'Reconciling withdrawal #123',
     *   ]);
     *
     * Usage (PHP 8.x - Named Arguments):
     *   $service->query(
     *       partyA: '600782',
     *       originalConversationId: 'WD123-abcdEFGH',
     *       remarks: 'Reconciling withdrawal #123'
     *   );
     *
     * @param array|string $params Array of parameters or partyA (shortcode)
     * @param string|null $transactionId M-Pesa receipt number, if known
     * @param string|null $originalConversationId The OriginatorConversationID to check on
     * @param string|null $remarks
     * @param string|null $occasion
     * @param int|null $identifierType 1=MSISDN, 2=Till, 4=Shortcode (default: 4)
     * @param string|null $callback
     * @return array
     */
    public function query(
        $params,
        ?string $transactionId = null,
        ?string $originalConversationId = null,
        ?string $remarks = null,
        ?string $occasion = null,
        ?int $identifierType = null,
        ?string $callback = null
    ): array {
        $data = $this->normalizeParams($params, [
            'transaction_id' => $transactionId,
            'original_conversation_id' => $originalConversationId,
            'remarks' => $remarks,
            'occasion' => $occasion,
            'identifier_type' => $identifierType,
            'callback' => $callback,
        ], 'party_a');

        $identifierType = $data['identifier_type'] ?? 4;
        $this->validateParams($data, $identifierType);

        $callback = $data['callback']
            ?? config('mpesa.transaction_status_callback_url')
            ?? config('mpesa.callback_url');
        $url = $this->client->getConfig('transactionstatus_url');

        $payload = [
            'Initiator' => $this->client->getConfig('initiator_name'),
            'SecurityCredential' => $this->client->generateSecurityCredential(),
            'CommandID' => 'TransactionStatusQuery',
            'PartyA' => $data['party_a'],
            'IdentifierType' => (string) $identifierType,
            'Remarks' => $data['remarks'],
            'QueueTimeOutURL' => "{$callback}/timeout",
            'ResultURL' => $callback,
            'Occasion' => $data['occasion'] ?? '',
        ];

        if (!empty($data['transaction_id'])) {
            $payload['TransactionID'] = $data['transaction_id'];
        }
        if (!empty($data['original_conversation_id'])) {
            $payload['OriginalConversationID'] = $data['original_conversation_id'];
        }

        return $this->client->post($url, $payload, 'b2c');
    }

    /**
     * Validate parameters
     */
    protected function validateParams(array $data, int $identifierType): void
    {
        $errors = [];

        if (empty($data['party_a'])) {
            $errors[] = 'PartyA (shortcode) is required';
        }

        if (!in_array($identifierType, [1, 2, 4], true)) {
            $errors[] = 'IdentifierType must be 1 (MSISDN), 2 (Till Number), or 4 (Organization shortcode)';
        }

        if (empty($data['remarks']) || strlen($data['remarks']) > 100) {
            $errors[] = 'Remarks is required and must be 100 characters or less';
        }

        if (empty($data['transaction_id']) && empty($data['original_conversation_id'])) {
            $errors[] = 'Either transactionId (M-Pesa receipt) or originalConversationId is required';
        }

        if (!empty($errors)) {
            throw new ValidationException(implode(', ', $errors));
        }
    }
}