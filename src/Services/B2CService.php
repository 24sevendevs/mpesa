<?php

namespace TFS\Mpesa\Services;

use TFS\Mpesa\MpesaClient;
use TFS\Mpesa\Support\Validator;
use TFS\Mpesa\Exceptions\ValidationException;

class B2CService
{
    protected MpesaClient $client;
    protected const VALID_COMMAND_IDS = ['SalaryPayment', 'BusinessPayment', 'PromotionPayment'];

    public function __construct(MpesaClient $client)
    {
        $this->client = $client;
    }

    /**
     * Execute B2C payment
     */
    public function pay(
        string $phone,
        string $amount,
        string $occasion,
        string $remarks,
        ?string $callback = null,
        ?string $commandId = null,
        ?string $shortcode = null
    ): array {
        $this->validateParams($phone, $amount, $occasion, $remarks);
        
        $commandId ??= 'BusinessPayment';
        $this->validateCommandId($commandId);

        $callback ??= config('mpesa.callback_url');
        $shortcode ??= $this->client->getConfig('b2c_shortcode');
        $url = $this->client->getConfig('b2c_url');

        $data = [
            'InitiatorName' => $this->client->getConfig('initiator_name'),
            'SecurityCredential' => $this->client->generateSecurityCredential(),
            'CommandID' => $commandId,
            'Amount' => $amount,
            'PartyA' => $shortcode,
            'PartyB' => $phone,
            'Remarks' => $remarks,
            'QueueTimeOutURL' => "{$callback}/timeout",
            'ResultURL' => $callback,
            'Occassion' => $occasion,
        ];

        return $this->client->post($url, $data, 'b2c');
    }

    /**
     * Validate parameters
     */
    protected function validateParams(
        string $phone,
        string $amount,
        string $occasion,
        string $remarks
    ): void {
        $errors = [];

        if (!Validator::isValidPhone($phone)) {
            $errors[] = 'Phone number must be valid (254XXXXXXXXX format)';
        }

        if (!Validator::isValidAmount($amount)) {
            $errors[] = 'Amount must be a positive number';
        }

        if (empty($occasion) || strlen($occasion) > 100) {
            $errors[] = 'Occasion is required and must be 100 characters or less';
        }

        if (empty($remarks) || strlen($remarks) > 100) {
            $errors[] = 'Remarks is required and must be 100 characters or less';
        }

        if (!empty($errors)) {
            throw new ValidationException(implode(', ', $errors));
        }
    }

    /**
     * Validate command ID
     */
    protected function validateCommandId(string $commandId): void
    {
        if (!in_array($commandId, self::VALID_COMMAND_IDS, true)) {
            throw new ValidationException(
                'Command ID must be one of: ' . implode(', ', self::VALID_COMMAND_IDS)
            );
        }
    }
}