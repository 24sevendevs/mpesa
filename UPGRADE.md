# Upgrade Guide

## Upgrading from 2.x to 3.0

Version 3.0 is a major rewrite with breaking changes. Please read this guide carefully.

### PHP Version Requirement

- **Minimum PHP**: 7.4
- **Recommended**: PHP 8.0+

### Laravel Version Support

- Laravel 8.x ✅
- Laravel 9.x ✅
- Laravel 10.x ✅
- Laravel 11.x ✅

### Breaking Changes

#### 1. Method Names Changed

**Old (v2.x):**
```php
Mpesa::mpesa_express($phone, $amount, $ref, $desc);
```

**New (v3.0):**
```php
Mpesa::stkPush(
    phone: $phone,
    amount: $amount,
    accountReference: $ref,
    transactionDesc: $desc
);

// Legacy method still works but is deprecated
Mpesa::mpesa_express($phone, $amount, $ref, $desc);
```

#### 2. Exception Handling Required

**Old (v2.x):**
```php
$response = Mpesa::b2c($phone, $amount, $occasion, $remarks);
if (isset($response['error'])) {
    // Handle error
}
```

**New (v3.0):**
```php
use TFS\Mpesa\Exceptions\{MpesaException, ValidationException};

try {
    $response = Mpesa::b2c(
        phone: $phone,
        amount: $amount,
        occasion: $occasion,
        remarks: $remarks
    );
} catch (ValidationException $e) {
    // Handle validation errors
} catch (MpesaException $e) {
    // Handle API errors
}
```

#### 3. B2B Method Split

**Old (v2.x):**
```php
Mpesa::b2b($partyB, $amount, $occasion, $remarks, $callback, $commandId);
```

**New (v3.0):**
```php
// For PayBill
Mpesa::b2bPayBill(
    partyB: $partyB,
    amount: $amount,
    accountReference: $reference,
    remarks: $remarks
);

// For BuyGoods
Mpesa::b2bBuyGoods(
    partyB: $partyB,
    amount: $amount,
    accountReference: $reference,
    remarks: $remarks
);

// For Pochi
Mpesa::b2Pochi(
    partyB: $partyB,
    amount: $amount,
    occasion: $occasion,
    remarks: $remarks,
    originatorConversationId: 'CONV-' . time()
);
```

#### 4. Balance Method Signature

**Old (v2.x):**
```php
Mpesa::balance(
    $partyA, 
    $remarks, 
    $callback, 
    $identifierType, 
    $consumer_key, 
    $consumer_secret, 
    $initiator_name, 
    $initiator_password
);
```

**New (v3.0):**
```php
// Using config credentials
Mpesa::balance(
    partyA: $partyA,
    remarks: $remarks,
    identifierType: $identifierType
);

// Using custom credentials
Mpesa::balance(
    partyA: $partyA,
    remarks: $remarks,
    identifierType: $identifierType,
    auth: [
        'b2c_consumer_key' => $consumer_key,
        'b2c_consumer_secret' => $consumer_secret,
        'initiator_name' => $initiator_name,
        'initiator_password' => $initiator_password
    ]
);
```

#### 5. C2B Registration

**Old (v2.x):**
```php
Mpesa::c2b_register_url(
    $ValidationURL, 
    $ConfirmationURL, 
    $ResponseType, 
    $ShortCode
);
```

**New (v3.0):**
```php
Mpesa::registerC2BUrl(
    validationUrl: $validationUrl,
    confirmationUrl: $confirmationUrl,
    responseType: 'Completed'
);
```

### New Features

#### Custom Authentication Per Request
```php
$response = Mpesa::stkPush(
    phone: '254712345678',
    amount: '100',
    accountReference: 'INV-123',
    transactionDesc: 'Payment',
    auth: [
        'consumer_key' => 'different_key',
        'consumer_secret' => 'different_secret',
        'shortcode' => '123456',
        'passkey' => 'different_passkey'
    ]
);
```

#### Token Caching

Tokens are now automatically cached for 58 minutes, reducing API calls.
```php
// No code changes needed - works automatically
```

### Configuration Updates

#### Old Config (v2.x)
```env
MPESA_CONSUMER_KEY=xxx
MPESA_CONSUMER_SECRET=xxx
```

#### New Config (v3.0)
```env
# Same as before, but now supports more options
MPESA_CONSUMER_KEY=xxx
MPESA_CONSUMER_SECRET=xxx
MPESA_B2C_CONSUMER_KEY=xxx
MPESA_B2C_CONSUMER_SECRET=xxx

# New callback structure
MPESA_CALLBACK_URL=https://yourdomain.com/api/mpesa/callback
MPESA_BALANCE_CALLBACK_URL=https://yourdomain.com/api/mpesa/balance
```

### Step-by-Step Migration

1. **Update Package**
```bash
   composer require tfs/mpesa:^3.0
```

2. **Republish Config**
```bash
   php artisan vendor:publish --provider="TFS\Mpesa\MpesaServiceProvider" --force
```

3. **Update Environment Variables**
   - Add new variables from config example above
   - Keep existing variables

4. **Update Code**
   - Replace all `mpesa_express()` with `stkPush()`
   - Split B2B calls into specific methods
   - Add try-catch blocks for exception handling
   - Update method signatures to use named parameters

5. **Test Thoroughly**
   - Test in sandbox mode first
   - Verify all callback URLs are working
   - Check error handling

### Need Help?

- [View Full Documentation](https://github.com/24sevendevs/mpesa)
- [Report Issues](https://github.com/24sevendevs/mpesa/issues)
- [See Examples](https://github.com/24sevendevs/mpesa/tree/main/examples)