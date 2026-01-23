<?php

namespace TFS\Mpesa\Services;

use TFS\Mpesa\MpesaClient;
use TFS\Mpesa\Support\NormalizesParameters;
use TFS\Mpesa\Exceptions\ValidationException;

class C2BService
{
    use NormalizesParameters;

    protected MpesaClient $client;

    /**
     * Key mapping from camelCase to snake_case
     */
    protected array $keyMap = [
        'validationUrl' => 'validation_url',
        'confirmationUrl' => 'confirmation_url',
        'responseType' => 'response_type',
        'shortCode' => 'short_code',
    ];

    public function __construct(MpesaClient $client)
    {
        $this->client = $client;
    }

    /**
     * Register C2B URLs
     *
     * @param array|string $params Array of parameters or validation URL
     * @param string|null $confirmationUrl
     * @param string|null $responseType 'Completed' or 'Canceled'
     * @param string|null $shortCode
     * @return array
     *
     * Usage (PHP 7.4 - Array):
     *   $service->registerUrls([
     *       'validation_url' => 'https://yourdomain.com/api/c2b/validation',
     *       'confirmation_url' => 'https://yourdomain.com/api/c2b/confirmation',
     *       'response_type' => 'Completed', // optional, default: 'Completed'
     *       'short_code' => '174379',       // optional
     *   ]);
     *
     * Usage (PHP 8.x - Named Arguments):
     *   $service->registerUrls(
     *       validationUrl: 'https://yourdomain.com/api/c2b/validation',
     *       confirmationUrl: 'https://yourdomain.com/api/c2b/confirmation',
     *       responseType: 'Completed'
     *   );
     *
     * Usage (Positional - Both versions):
     *   $service->registerUrls(
     *       'https://yourdomain.com/api/c2b/validation',
     *       'https://yourdomain.com/api/c2b/confirmation',
     *       'Completed'
     *   );
     */
    public function registerUrls(
        $params,
        ?string $confirmationUrl = null,
        ?string $responseType = null,
        ?string $shortCode = null
    ): array {
        // Normalize parameters
        $data = $this->normalizeParams($params, [
            'confirmation_url' => $confirmationUrl,
            'response_type' => $responseType,
            'short_code' => $shortCode,
        ], 'validation_url');

        // Set defaults
        $responseType = $data['response_type'] ?? 'Completed';

        // Validate
        $this->validateParams($data, $responseType);

        $shortCode = $data['short_code'] ?? $this->client->getConfig('shortcode');
        $url = $this->client->getConfig('c2b_register_url');

        $payload = [
            'ValidationURL' => $data['validation_url'],
            'ConfirmationURL' => $data['confirmation_url'],
            'ResponseType' => $responseType,
            'ShortCode' => $shortCode,
        ];

        return $this->client->post($url, $payload);
    }

    /**
     * Validate parameters
     */
    protected function validateParams(array $data, string $responseType): void
    {
        $errors = [];

        if (empty($data['validation_url'])) {
            $errors[] = 'ValidationURL is required';
        } elseif (!filter_var($data['validation_url'], FILTER_VALIDATE_URL)) {
            $errors[] = 'ValidationURL must be a valid URL';
        }

        if (empty($data['confirmation_url'])) {
            $errors[] = 'ConfirmationURL is required';
        } elseif (!filter_var($data['confirmation_url'], FILTER_VALIDATE_URL)) {
            $errors[] = 'ConfirmationURL must be a valid URL';
        }

        if (!in_array($responseType, ['Completed', 'Canceled'], true)) {
            $errors[] = 'ResponseType must be either "Completed" or "Canceled"';
        }

        if (!empty($errors)) {
            throw new ValidationException(implode(', ', $errors));
        }
    }
}