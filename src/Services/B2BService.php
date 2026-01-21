<?php

namespace TFS\Mpesa\Services;

use TFS\Mpesa\MpesaClient;
use TFS\Mpesa\Support\Validator;
use TFS\Mpesa\Exceptions\ValidationException;

class B2BService
{
    protected MpesaClient $client;

    public function __construct(MpesaClient $client)
    {
        $this->client = $client;
    }

    /**
     * Business PayBill payment
     */
    public function payBill(
        string $partyB,
        string $amount,
        string $accountReference,
        string $remarks,
        ?string $requester = null,
        ?string $callback = null,
        ?string $shortcode = null
    ): array {
        return $this->execute(
            commandId: 'BusinessPayBill',
            partyB: $partyB,
            amount: $amount,
            accountReference: $accountReference,
            remarks: $remarks,
            requester: $requester,
            callback: $callback,
            shortcode: $shortcode
        );
    }

    /**
     * Business BuyGoods payment
     */
    public function buyGoods(
        string $partyB,
        string $amount,
        string $accountReference,
        string $remarks,
        ?string $requester = null,
        ?string $callback = null,
        ?string $shortcode = null
    ): array {
        return $this->execute(
            commandId: 'BusinessBuyGoods',
            partyB: $partyB,
            amount: $amount,
            accountReference: $accountReference,
            remarks: $remarks,
            requester: $requester,
            callback: $callback,
            shortcode: $shortcode
        );
    }

    /**
     * Pay to Pochi (Business Wallet)
     */
    public function payToPochi(
        string $partyB,
        string $amount,
        string $occasion,
        string $remarks,
        string $originatorConversationId,
        ?string $callback = null,
        ?string $shortcode = null
    ): array {
        $this->validatePochiParams($partyB, $amount, $occasion, $remarks, $originatorConversationId);

        $callback ??= config('mpesa.callback_url');
        $shortcode ??= $this->client->getConfig('b2c_shortcode');
        $url = $this->client->getConfig('b2pochi_url');

        $data = [
            'InitiatorName' => $this->client->getConfig('initiator_name'),
            'SecurityCredential' => $this->client->generateSecurityCredential(),
            'CommandID' => 'BusinessPayToPochi',
            'PartyA' => $shortcode,
            'PartyB' => $partyB,
            'Remarks' => $remarks,
            'OriginatorConversationID' => $originatorConversationId,
            'Amount' => $amount,
            'ResultURL' => $callback,
            'QueueTimeOutURL' => "{$callback}/timeout",
            'Occassion' => $occasion,
        ];

        return $this->client->post($url, $data, 'b2c');
    }

    /**
     * Execute B2B transaction
     */
    protected function execute(
        string $commandId,
        string $partyB,
        string $amount,
        string $accountReference,
        string $remarks,
        ?string $requester,
        ?string $callback,
        ?string $shortcode
    ): array {
        $this->validateParams($partyB, $amount, $accountReference, $remarks);

        $callback ??= config('mpesa.callback_url');
        $shortcode ??= $this->client->getConfig('b2c_shortcode');
        $url = $this->client->getConfig('b2b_url');

        $data = [
            'Initiator' => $this->client->getConfig('initiator_name'),
            'SecurityCredential' => $this->client->generateSecurityCredential(),
            'CommandID' => $commandId,
            'SenderIdentifierType' => '4',
            'RecieverIdentifierType' => '4',
            'Amount' => $amount,
            'PartyA' => $shortcode,
            'PartyB' => $partyB,
            'AccountReference' => $accountReference,
            'Remarks' => $remarks,
            'QueueTimeOutURL' => "{$callback}/timeout",
            'ResultURL' => $callback,
        ];

        if ($requester) {
            $data['Requester'] = $requester;
        }

        return $this->client->post($url, $data, 'b2c');
    }

    /**
     * Validate B2B parameters
     */
    protected function validateParams(
        string $partyB,
        string $amount,
        string $accountReference,
        string $remarks
    ): void {
        $errors = [];

        if (empty($partyB)) {
            $errors[] = 'PartyB (recipient shortcode) is required';
        }

        if (!Validator::isValidAmount($amount)) {
            $errors[] = 'Amount must be a positive number';
        }

        if (empty($accountReference) || strlen($accountReference) > 13) {
            $errors[] = 'AccountReference is required and must be 13 characters or less';
        }

        if (empty($remarks) || strlen($remarks) > 100) {
            $errors[] = 'Remarks is required and must be 100 characters or less';
        }

        if (!empty($errors)) {
            throw new ValidationException(implode(', ', $errors));
        }
    }

    /**
     * Validate B2Pochi parameters
     */
    protected function validatePochiParams(
        string $partyB,
        string $amount,
        string $occasion,
        string $remarks,
        string $originatorConversationId
    ): void {
        $errors = [];

        if (empty($partyB)) {
            $errors[] = 'PartyB (customer phone number) is required';
        }

        if (!Validator::isValidAmount($amount)) {
            $errors[] = 'Amount must be a positive number';
        }

        if (empty($occasion) || strlen($occasion) < 1 || strlen($occasion) > 100) {
            $errors[] = 'Occasion must be between 1 and 100 characters';
        }

        if (empty($remarks) || strlen($remarks) < 2 || strlen($remarks) > 100) {
            $errors[] = 'Remarks must be between 2 and 100 characters';
        }

        if (empty($originatorConversationId)) {
            $errors[] = 'OriginatorConversationID is required';
        }

        if (!empty($errors)) {
            throw new ValidationException(implode(', ', $errors));
        }
    }
}