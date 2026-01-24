<?php

namespace TFS\Mpesa\Services;

use TFS\Mpesa\MpesaClient;
use TFS\Mpesa\Support\Validator;
use TFS\Mpesa\Support\NormalizesParameters;
use TFS\Mpesa\Exceptions\ValidationException;

class B2CService
{
    use NormalizesParameters;

    protected MpesaClient $client;
    protected const VALID_COMMAND_IDS = ['SalaryPayment', 'BusinessPayment', 'PromotionPayment'];

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
            'commandId' => 'command_id',
        ];
    }

    /**
     * Execute B2C payment
     *
     * @param array|string $params Array of parameters or phone number
     * @param string|null $amount
     * @param string|null $occasion
     * @param string|null $remarks
     * @param string|null $callback
     * @param string|null $commandId
     * @param string|null $shortcode
     * @return array
     *
     * Usage (PHP 7.4 - Array):
     *   $service->pay([
     *       'phone' => '254712345678',
     *       'amount' => '1000',
     *       'occasion' => 'Salary',
     *       'remarks' => 'Monthly salary',
     *       'command_id' => 'SalaryPayment', // optional
     *       'callback' => 'https://...',     // optional
     *   ]);
     *
     * Usage (PHP 8.x - Named Arguments):
     *   $service->pay(
     *       phone: '254712345678',
     *       amount: '1000',
     *       occasion: 'Salary',
     *       remarks: 'Monthly salary',
     *       commandId: 'SalaryPayment'
     *   );
     *
     * Usage (Positional - Both versions):
     *   $service->pay('254712345678', '1000', 'Salary', 'Monthly salary');
     */
    public function pay(
        $params,
        ?string $amount = null,
        ?string $occasion = null,
        ?string $remarks = null,
        ?string $callback = null,
        ?string $commandId = null,
        ?string $shortcode = null
    ): array {
        // Normalize parameters
        $data = $this->normalizeParams($params, [
            'amount' => $amount,
            'occasion' => $occasion,
            'remarks' => $remarks,
            'callback' => $callback,
            'command_id' => $commandId,
            'shortcode' => $shortcode,
        ], 'phone');

        $this->validateParams($data);

        $commandId = $data['command_id'] ?? 'BusinessPayment';
        $this->validateCommandId($commandId);

        $callback = $data['callback'] ?? config('mpesa.callback_url');
        $shortcode = $data['shortcode'] ?? $this->client->getConfig('b2c_shortcode');
        $url = $this->client->getConfig('b2c_url');

        $payload = [
            'InitiatorName' => $this->client->getConfig('initiator_name'),
            'SecurityCredential' => $this->client->generateSecurityCredential(),
            'CommandID' => $commandId,
            'Amount' => $data['amount'],
            'PartyA' => $shortcode,
            'PartyB' => $data['phone'],
            'Remarks' => $data['remarks'],
            'QueueTimeOutURL' => "{$callback}/timeout",
            'ResultURL' => $callback,
            'Occassion' => $data['occasion'],
        ];

        return $this->client->post($url, $payload, 'b2c');
    }

    /**
     * Validate parameters
     */
    protected function validateParams(array $data): void
    {
        $errors = [];

        if (!isset($data['phone']) || !Validator::isValidPhone($data['phone'])) {
            $errors[] = 'Phone number must be valid (254XXXXXXXXX format)';
        }

        if (!isset($data['amount']) || !Validator::isValidAmount($data['amount'])) {
            $errors[] = 'Amount must be a positive number';
        }

        if (empty($data['occasion']) || strlen($data['occasion']) > 100) {
            $errors[] = 'Occasion is required and must be 100 characters or less';
        }

        if (empty($data['remarks']) || strlen($data['remarks']) > 100) {
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