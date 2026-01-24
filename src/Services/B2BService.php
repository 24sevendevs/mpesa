<?php

namespace TFS\Mpesa\Services;

use TFS\Mpesa\MpesaClient;
use TFS\Mpesa\Support\Validator;
use TFS\Mpesa\Support\NormalizesParameters;
use TFS\Mpesa\Exceptions\ValidationException;

class B2BService
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
            'partyB' => 'party_b',
            'accountReference' => 'account_reference',
            'originatorConversationId' => 'originator_conversation_id',
            'commandId' => 'command_id',
        ];
    }

    /**
     * Business PayBill payment
     *
     * Usage (PHP 7.4 - Array):
     *   $service->payBill([
     *       'party_b' => '888880',
     *       'amount' => '1000',
     *       'account_reference' => 'ACC-123',
     *       'remarks' => 'Payment',
     *       'requester' => '254712345678', // optional
     *   ]);
     *
     * Usage (PHP 8.x - Named Arguments):
     *   $service->payBill(
     *       partyB: '888880',
     *       amount: '1000',
     *       accountReference: 'ACC-123',
     *       remarks: 'Payment'
     *   );
     */
    public function payBill(
        $params,
        ?string $amount = null,
        ?string $accountReference = null,
        ?string $remarks = null,
        ?string $requester = null,
        ?string $callback = null,
        ?string $shortcode = null
    ): array {
        $data = $this->normalizeParams($params, [
            'amount' => $amount,
            'account_reference' => $accountReference,
            'remarks' => $remarks,
            'requester' => $requester,
            'callback' => $callback,
            'shortcode' => $shortcode,
        ], 'party_b');

        $data['command_id'] = 'BusinessPayBill';

        return $this->execute($data);
    }

    /**
     * Business BuyGoods payment
     *
     * Usage (PHP 7.4 - Array):
     *   $service->buyGoods([
     *       'party_b' => '888880',
     *       'amount' => '500',
     *       'account_reference' => 'ORDER-789',
     *       'remarks' => 'Payment for goods',
     *   ]);
     *
     * Usage (PHP 8.x - Named Arguments):
     *   $service->buyGoods(
     *       partyB: '888880',
     *       amount: '500',
     *       accountReference: 'ORDER-789',
     *       remarks: 'Payment for goods'
     *   );
     */
    public function buyGoods(
        $params,
        ?string $amount = null,
        ?string $accountReference = null,
        ?string $remarks = null,
        ?string $requester = null,
        ?string $callback = null,
        ?string $shortcode = null
    ): array {
        $data = $this->normalizeParams($params, [
            'amount' => $amount,
            'account_reference' => $accountReference,
            'remarks' => $remarks,
            'requester' => $requester,
            'callback' => $callback,
            'shortcode' => $shortcode,
        ], 'party_b');

        $data['command_id'] = 'BusinessBuyGoods';

        return $this->execute($data);
    }

    /**
     * Pay to Pochi (Business Wallet)
     *
     * Usage (PHP 7.4 - Array):
     *   $service->payToPochi([
     *       'party_b' => '254712345678',
     *       'amount' => '1000',
     *       'occasion' => 'Payment',
     *       'remarks' => 'Business payment',
     *       'originator_conversation_id' => 'CONV-123',
     *   ]);
     *
     * Usage (PHP 8.x - Named Arguments):
     *   $service->payToPochi(
     *       partyB: '254712345678',
     *       amount: '1000',
     *       occasion: 'Payment',
     *       remarks: 'Business payment',
     *       originatorConversationId: 'CONV-123'
     *   );
     */
    public function payToPochi(
        $params,
        ?string $amount = null,
        ?string $occasion = null,
        ?string $remarks = null,
        ?string $originatorConversationId = null,
        ?string $callback = null,
        ?string $shortcode = null
    ): array {
        $data = $this->normalizeParams($params, [
            'amount' => $amount,
            'occasion' => $occasion,
            'remarks' => $remarks,
            'originator_conversation_id' => $originatorConversationId,
            'callback' => $callback,
            'shortcode' => $shortcode,
        ], 'party_b');

        $this->validatePochiParams($data);

        $callback = $data['callback'] ?? config('mpesa.callback_url');
        $shortcode = $data['shortcode'] ?? $this->client->getConfig('b2c_shortcode');
        $url = $this->client->getConfig('b2pochi_url');

        $payload = [
            'InitiatorName' => $this->client->getConfig('initiator_name'),
            'SecurityCredential' => $this->client->generateSecurityCredential(),
            'CommandID' => 'BusinessPayToPochi',
            'PartyA' => $shortcode,
            'PartyB' => $data['party_b'],
            'Remarks' => $data['remarks'],
            'OriginatorConversationID' => $data['originator_conversation_id'],
            'Amount' => $data['amount'],
            'ResultURL' => $callback,
            'QueueTimeOutURL' => "{$callback}/timeout",
            'Occassion' => $data['occasion'],
        ];

        return $this->client->post($url, $payload, 'b2c');
    }

    /**
     * Execute B2B transaction
     */
    protected function execute(array $data): array
    {
        $this->validateParams($data);

        $callback = $data['callback'] ?? config('mpesa.callback_url');
        $shortcode = $data['shortcode'] ?? $this->client->getConfig('b2c_shortcode');
        $url = $this->client->getConfig('b2b_url');

        $payload = [
            'Initiator' => $this->client->getConfig('initiator_name'),
            'SecurityCredential' => $this->client->generateSecurityCredential(),
            'CommandID' => $data['command_id'],
            'SenderIdentifierType' => '4',
            'RecieverIdentifierType' => '4',
            'Amount' => $data['amount'],
            'PartyA' => $shortcode,
            'PartyB' => $data['party_b'],
            'AccountReference' => $data['account_reference'],
            'Remarks' => $data['remarks'],
            'QueueTimeOutURL' => "{$callback}/timeout",
            'ResultURL' => $callback,
        ];

        if (!empty($data['requester'])) {
            $payload['Requester'] = $data['requester'];
        }

        return $this->client->post($url, $payload, 'b2c');
    }

    /**
     * Validate B2B parameters
     */
    protected function validateParams(array $data): void
    {
        $errors = [];

        if (empty($data['party_b'])) {
            $errors[] = 'PartyB (recipient shortcode) is required';
        }

        if (!isset($data['amount']) || !Validator::isValidAmount($data['amount'])) {
            $errors[] = 'Amount must be a positive number';
        }

        if (empty($data['account_reference']) || strlen($data['account_reference']) > 13) {
            $errors[] = 'AccountReference is required and must be 13 characters or less';
        }

        if (empty($data['remarks']) || strlen($data['remarks']) > 100) {
            $errors[] = 'Remarks is required and must be 100 characters or less';
        }

        if (!empty($errors)) {
            throw new ValidationException(implode(', ', $errors));
        }
    }

    /**
     * Validate B2Pochi parameters
     */
    protected function validatePochiParams(array $data): void
    {
        $errors = [];

        if (empty($data['party_b'])) {
            $errors[] = 'PartyB (customer phone number) is required';
        }

        if (!isset($data['amount']) || !Validator::isValidAmount($data['amount'])) {
            $errors[] = 'Amount must be a positive number';
        }

        if (empty($data['occasion']) || strlen($data['occasion']) > 100) {
            $errors[] = 'Occasion must be between 1 and 100 characters';
        }

        if (empty($data['remarks']) || strlen($data['remarks']) < 2 || strlen($data['remarks']) > 100) {
            $errors[] = 'Remarks must be between 2 and 100 characters';
        }

        if (empty($data['originator_conversation_id'])) {
            $errors[] = 'OriginatorConversationID is required';
        }

        if (!empty($errors)) {
            throw new ValidationException(implode(', ', $errors));
        }
    }
}