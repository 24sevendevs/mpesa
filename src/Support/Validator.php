<?php

namespace TFS\Mpesa\Support;

class Validator
{
    public static function isValidPhone(string $phone): bool
    {
        return preg_match('/^254[17]\d{8}$/', $phone) === 1;
    }

    public static function isValidAmount(string $amount): bool
    {
        return is_numeric($amount) && $amount > 0;
    }
}