<?php

namespace TFS\Mpesa\Support;

/**
 * Trait for normalizing parameters across PHP 7.4 (arrays) and PHP 8.x (named arguments)
 *
 * Usage:
 *   1. Add `use NormalizesParameters;` in your service class
 *   2. Implement `getKeyMap()` method returning camelCase => snake_case mappings
 *   3. Call `$this->normalizeParams($params, [...], 'first_key')` in your methods
 *   4. Use `$this->getParam($data, 'key', $default)` to retrieve values
 */
trait NormalizesParameters
{
    /**
     * Get key mapping from camelCase to snake_case
     * Override this in your service class
     *
     * Example:
     *   protected function getKeyMap(): array
     *   {
     *       return [
     *           'accountReference' => 'account_reference',
     *           'transactionDesc' => 'transaction_desc',
     *       ];
     *   }
     *
     * @return array
     */
    protected function getKeyMap(): array
    {
        return [];
    }

    /**
     * Normalize parameters from various input formats
     *
     * Supports:
     * - Array input (PHP 7.4+): ['phone' => '254...', 'amount' => '100']
     * - Named arguments (PHP 8.0+): phone: '254...', amount: '100'
     * - Positional arguments: '254...', '100', ...
     *
     * @param array|string|int $firstParam Array of all params OR first positional param
     * @param array $additionalParams Key-value pairs of additional positional params
     * @param string $firstParamKey Key name for first positional param
     * @return array Normalized parameters array
     */
    protected function normalizeParams($firstParam, array $additionalParams, string $firstParamKey): array
    {
        // If first param is an array, use it directly (array style)
        if (is_array($firstParam)) {
            return $this->normalizeKeys($firstParam);
        }

        // Build array from individual parameters (positional/named style)
        $data = [$firstParamKey => $firstParam];

        foreach ($additionalParams as $key => $value) {
            if ($value !== null) {
                $data[$key] = $value;
            }
        }

        return $data;
    }

    /**
     * Normalize array keys to snake_case
     *
     * Supports both camelCase and snake_case input:
     * - 'accountReference' => 'account_reference'
     * - 'account_reference' => 'account_reference'
     *
     * @param array $params
     * @return array
     */
    protected function normalizeKeys(array $params): array
    {
        $normalized = [];
        $keyMap = $this->getKeyMap();

        foreach ($params as $key => $value) {
            // Check if key needs mapping
            $normalizedKey = $keyMap[$key] ?? $key;
            $normalized[$normalizedKey] = $value;
        }

        return $normalized;
    }

    /**
     * Extract 'auth' key from params array if present
     *
     * @param array|mixed $params
     * @param array|null $auth
     * @return array [params, auth]
     */
    protected function extractAuth($params, ?array $auth): array
    {
        if (is_array($params) && isset($params['auth'])) {
            $auth = $params['auth'];
            unset($params['auth']);
        }

        return [$params, $auth];
    }

    /**
     * Get value from normalized params with default
     *
     * @param array $params
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function getParam(array $params, string $key, $default = null)
    {
        return $params[$key] ?? $default;
    }
}