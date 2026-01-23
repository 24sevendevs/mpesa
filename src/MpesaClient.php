<?php

namespace TFS\Mpesa;

use Illuminate\Support\Facades\{Http, Cache};
use TFS\Mpesa\Support\SecurityCredential;
use TFS\Mpesa\Exceptions\MpesaException;

class MpesaClient
{
    protected string $mode;
    protected array $config;
    protected ?array $customAuth;

    public function __construct(?array $auth = null)
    {
        $this->mode = config('mpesa.mode', 'live');
        $this->config = config("mpesa.{$this->mode}", []);
        $this->customAuth = $auth;
    }

    /**
     * Get access token with caching
     */
    public function getAccessToken(string $type = 'c2b'): string
    {
        $consumerKey = $this->getConfig($type === 'b2c' ? 'b2c_consumer_key' : 'consumer_key');
        $consumerSecret = $this->getConfig($type === 'b2c' ? 'b2c_consumer_secret' : 'consumer_secret');

        $cacheKey = "mpesa_token_{$consumerKey}_{$consumerSecret}";


        return Cache::remember($cacheKey, 3500, function () use ($type) {

            $tokenUrl = $this->getConfig('token_url');

            $response = Http::retry(3, 100)
                ->withBasicAuth($consumerKey, $consumerSecret)
                ->get($tokenUrl);

            if (!$response->successful()) {
                throw new MpesaException("Failed to get access token: " . $response->body());
            }

            $data = $response->json();

            if (!isset($data['access_token'])) {
                throw new MpesaException("Access token not found in response");
            }

            return $data['access_token'];
        });
    }

    /**
     * Make POST request to M-Pesa API
     */
    public function post(string $url, array $data, string $tokenType = 'c2b'): array
    {
        $accessToken = $this->getAccessToken($tokenType);

        $response = Http::retry(3, 100)
            ->withHeaders([
                'Authorization' => "Bearer {$accessToken}",
                'Content-Type' => 'application/json',
            ])
            ->post($url, $data);

        if (!$response->successful()) {
            throw new MpesaException(
                "M-Pesa API request failed: " . $response->body(),
                $response->status()
            );
        }

        return $response->json() ?? [];
    }

    /**
     * Get configuration value with custom auth override
     */
    public function getConfig(string $key, $default = null)
    {
        // Check custom auth first
        if ($this->customAuth && isset($this->customAuth[$key])) {
            return $this->customAuth[$key];
        }

        // Fall back to config
        return $this->config[$key] ?? config("mpesa.{$key}", $default);
    }

    /**
     * Generate security credential
     */
    public function generateSecurityCredential(?string $password = null): string
    {
        $password ??= $this->getConfig('initiator_password');
        return SecurityCredential::generate($password, $this->mode);
    }

    /**
     * Get current mode
     */
    public function getMode(): string
    {
        return $this->mode;
    }
}
