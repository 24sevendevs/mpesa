<?php

namespace TFS\Mpesa\Support;

class SecurityCredential
{
    public static function generate(string $password, string $mode): string
    {
        if ($mode === 'sandbox') {
            return 'XMiKlEz4iuquErci7bL3nF/T8Ej5NdrHB4aUvjczqkikaocdTnVw3s1mQlzhMNZqtRSqqEWrQAhQT3OwkiYfHKBf1YUnykxXUo6UO1eXM82+0k6ZEVb90JEAoTvCOK9JEOPEFusqMRtSrxca4gU3qEA0CyLpY3k7ZWLiNisuaWWL2zDJSlRBBz8bn4waOLuLLz3aB1NVQYaxtlLjf6ITah7q2nx2lt1NKCkCImg/e/rKfJTzrmgRHbV2+3MC4t4SKJRwMosHBXd0FjOzFY5IO1/b7EBbwcmMIZMsuyFhnlSvjqolllFc9SToK37h+G5TMhZthJBA3PfkAWyjJK6nqQ==';
        }

        $publicKey = file_get_contents(__DIR__ . '/../assets/public_keycert.cer');

        if (!$publicKey) {
            throw new \RuntimeException('Unable to read public key certificate');
        }

        $encrypted = '';
        $result = openssl_public_encrypt(
            $password,
            $encrypted,
            $publicKey,
            OPENSSL_PKCS1_PADDING
        );

        if (!$result) {
            throw new \RuntimeException('Failed to encrypt initiator password');
        }

        return base64_encode($encrypted);
    }
}