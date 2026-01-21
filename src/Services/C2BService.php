<?php

namespace TFS\Mpesa\Services;

use TFS\Mpesa\MpesaClient;
use TFS\Mpesa\Exceptions\ValidationException;

class C2BService
{
    protected MpesaClient $client;

    public function __construct(MpesaClient $client)
    {
        $this->client = $client;
    }

    public function registerUrls(
        string $validationUrl,
        string $confirmationUrl,
        string $responseType = 'Completed',
        ?string $shortCode = null
    ): array {
        if (!in_array($responseType, ['Completed', 'Canceled'], true)) {
            throw new ValidationException('ResponseType must be either "Completed" or "Canceled"');
        }

        $shortCode ??= $this->client->getConfig('shortcode');
        $url = $this->client->getConfig('c2b_register_url');

        $data = [
            'ValidationURL' => $validationUrl,
            'ConfirmationURL' => $confirmationUrl,
            'ResponseType' => $responseType,
            'ShortCode' => $shortCode,
        ];

        return $this->client->post($url, $data);
    }
}