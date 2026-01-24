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
        $this->mode = $auth['mode'] ?? config('mpesa.mode', 'live');
        $this->config = config("mpesa.{$this->mode}", []);
        $this->customAuth = $auth;
    }

    /**
     * Get access token with caching
     *
     * @param string $type 'c2b' or 'b2c'
     * @return string
     * @throws MpesaException
     */
    public function getAccessToken(string $type = 'c2b'): string
    {
        // Get credentials - custom auth takes priority
        $consumerKey = $this->getCredential($type, 'consumer_key');
        $consumerSecret = $this->getCredential($type, 'consumer_secret');

        if (empty($consumerKey) || empty($consumerSecret)) {
            throw new MpesaException("Consumer key and secret are required for {$type} authentication");
        }

        // Cache key based on actual credentials used
        $cacheKey = "mpesa_token_" . md5($consumerKey . $consumerSecret);

        return Cache::remember($cacheKey, 3500, function () use ($consumerKey, $consumerSecret) {
            $tokenUrl = $this->getConfig('token_url');

            if (empty($tokenUrl)) {
                throw new MpesaException("Token URL is not configured");
            }

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
     * Get credential based on type (c2b or b2c)
     *
     * Priority:
     * 1. Custom auth with exact key (e.g., 'consumer_key')
     * 2. Custom auth with prefixed key (e.g., 'b2c_consumer_key')
     * 3. Config with prefixed key for b2c
     * 4. Config with exact key
     *
     * @param string $type 'c2b' or 'b2c'
     * @param string $key 'consumer_key' or 'consumer_secret'
     * @return string|null
     */
    protected function getCredential(string $type, string $key): ?string
    {
        $prefixedKey = $type === 'b2c' ? "b2c_{$key}" : $key;

        // 1. Check custom auth for exact key first
        if ($this->customAuth && isset($this->customAuth[$key])) {
            return $this->customAuth[$key];
        }

        // 2. Check custom auth for prefixed key (b2c_consumer_key)
        if ($this->customAuth && isset($this->customAuth[$prefixedKey])) {
            return $this->customAuth[$prefixedKey];
        }

        // 3. For b2c, check config with prefix first
        if ($type === 'b2c') {
            $value = $this->config[$prefixedKey] ?? null;
            if ($value) {
                return $value;
            }
        }

        // 4. Fall back to config with exact key
        return $this->config[$key] ?? config("mpesa.{$key}");
    }

    /**
     * Make POST request to M-Pesa API
     *
     * @param string $url
     * @param array $data
     * @param string $tokenType 'c2b' or 'b2c'
     * @return array
     * @throws MpesaException
     */
    public function post(string $url, array $data, string $tokenType = 'c2b'): array
    {
        $accessToken = $this->getAccessToken($tokenType);

        $response = Http::withHeaders([
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
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
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
     *
     * @param string|null $password
     * @return string
     */
    public function generateSecurityCredential(?string $password = null): string
    {
        $password ??= $this->getConfig('initiator_password');
        
        if (empty($password)) {
            throw new MpesaException("Initiator password is required for security credential generation");
        }
        
        return SecurityCredential::generate($password, $this->mode);
    }

    /**
     * Get current mode
     *
     * @return string
     */
    public function getMode(): string
    {
        return $this->mode;
    }
}