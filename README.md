# Safaricom M-Pesa Daraja API Laravel Package

[![Issues](https://img.shields.io/github/issues/24sevendevs/mpesa?style=flat-square)](https://github.com/24sevendevs/mpesa/issues)
[![Stars](https://img.shields.io/github/stars/24sevendevs/mpesa?style=flat-square)](https://github.com/24sevendevs/mpesa/stargazers)

A modern Laravel package for Safaricom's M-Pesa Daraja API. Supports **PHP 7.4+** and **PHP 8.x** with a flexible API that works with both array parameters and named arguments.

## Features

- ✅ **STK Push (M-Pesa Express)** - Prompt customers to pay via M-Pesa
- ✅ **B2C Payments** - Send money to customers
- ✅ **B2B Payments** - PayBill, BuyGoods, PayToPochi
- ✅ **Account Balance** - Check your M-Pesa balance
- ✅ **C2B URL Registration** - Register validation/confirmation URLs
- ✅ **Dual API Style** - Arrays (PHP 7.4+) or Named Arguments (PHP 8.0+)
- ✅ **Token Caching** - Automatic access token management
- ✅ **Custom Auth** - Override credentials per transaction

## Requirements

| PHP Version | Supported | Recommended Style |
|-------------|-----------|-------------------|
| 7.4         | ✅        | Array parameters  |
| 8.0+        | ✅        | Named arguments   |

## Installation

```bash
composer require tfs/mpesa:^3.0
```

Publish configuration:

```bash
php artisan vendor:publish --provider="TFS\Mpesa\MpesaServiceProvider"
```

## Usage Styles

This package supports **two parameter styles** for maximum compatibility:

### Style 1: Array Parameters (PHP 7.4+)

```php
use TFS\Mpesa\Mpesa;

$response = Mpesa::stkPush([
    'phone' => '254712345678',
    'amount' => '100',
    'account_reference' => 'INV-12345',
    'transaction_desc' => 'Payment for order',
]);
```

### Style 2: Named Arguments (PHP 8.0+)

```php
use TFS\Mpesa\Mpesa;

$response = Mpesa::stkPush(
    phone: '254712345678',
    amount: '100',
    accountReference: 'INV-12345',
    transactionDesc: 'Payment for order'
);
```

### Style 3: Positional Arguments (Both)

```php
$response = Mpesa::stkPush('254712345678', '100', 'INV-12345', 'Payment');
```

---

## API Reference

### STK Push (M-Pesa Express)

Prompt a customer to enter their M-Pesa PIN:

```php
// PHP 7.4+ (Array)
$response = Mpesa::stkPush([
    'phone' => '254712345678',
    'amount' => '100',
    'account_reference' => 'INV-12345',
    'transaction_desc' => 'Payment for order',
    'callback' => 'https://yourdomain.com/callback', // optional
]);

// PHP 8.0+ (Named Arguments)
$response = Mpesa::stkPush(
    phone: '254712345678',
    amount: '100',
    accountReference: 'INV-12345',
    transactionDesc: 'Payment for order',
    callback: 'https://yourdomain.com/callback'
);
```

#### Query STK Push Status

```php
// PHP 7.4+
$response = Mpesa::stkQuery(['checkout_request_id' => 'ws_CO_123456']);

// PHP 8.0+
$response = Mpesa::stkQuery(checkoutRequestId: 'ws_CO_123456');

// Positional
$response = Mpesa::stkQuery('ws_CO_123456');
```

---

### B2C (Business to Customer)

Send money to a customer:

```php
// PHP 7.4+ (Array)
$response = Mpesa::b2c([
    'phone' => '254712345678',
    'amount' => '1000',
    'occasion' => 'Salary',
    'remarks' => 'Monthly salary payment',
    'command_id' => 'SalaryPayment', // SalaryPayment | BusinessPayment | PromotionPayment
]);

// PHP 8.0+ (Named Arguments)
$response = Mpesa::b2c(
    phone: '254712345678',
    amount: '1000',
    occasion: 'Salary',
    remarks: 'Monthly salary payment',
    commandId: 'SalaryPayment'
);
```

---

### B2B PayBill

Pay a bill to another business:

```php
// PHP 7.4+
$response = Mpesa::b2bPayBill([
    'party_b' => '888880',
    'amount' => '1000',
    'account_reference' => 'ACC-12345',
    'remarks' => 'Payment for services',
    'requester' => '254712345678', // optional - on behalf of
]);

// PHP 8.0+
$response = Mpesa::b2bPayBill(
    partyB: '888880',
    amount: '1000',
    accountReference: 'ACC-12345',
    remarks: 'Payment for services'
);
```

---

### B2B BuyGoods

Pay to a till number:

```php
// PHP 7.4+
$response = Mpesa::b2bBuyGoods([
    'party_b' => '888880',
    'amount' => '500',
    'account_reference' => 'ORDER-789',
    'remarks' => 'Payment for goods',
]);

// PHP 8.0+
$response = Mpesa::b2bBuyGoods(
    partyB: '888880',
    amount: '500',
    accountReference: 'ORDER-789',
    remarks: 'Payment for goods'
);
```

---

### B2 Pochi (Business Wallet)

Send to a business wallet:

```php
// PHP 7.4+
$response = Mpesa::b2Pochi([
    'party_b' => '254712345678',
    'amount' => '1000',
    'occasion' => 'Payment',
    'remarks' => 'Business payment',
    'originator_conversation_id' => 'CONV-' . time(),
]);

// PHP 8.0+
$response = Mpesa::b2Pochi(
    partyB: '254712345678',
    amount: '1000',
    occasion: 'Payment',
    remarks: 'Business payment',
    originatorConversationId: 'CONV-' . time()
);
```

---

### Account Balance

Check your M-Pesa balance:

```php
// PHP 7.4+
$response = Mpesa::balance([
    'party_a' => '600000',
    'remarks' => 'Balance inquiry',
    'identifier_type' => 4, // 1=MSISDN, 2=Till, 4=Shortcode
]);

// PHP 8.0+
$response = Mpesa::balance(
    partyA: '600000',
    remarks: 'Balance inquiry',
    identifierType: 4
);
```

---

### C2B URL Registration

Register validation and confirmation URLs:

```php
// PHP 7.4+
$response = Mpesa::registerC2BUrl([
    'validation_url' => 'https://yourdomain.com/api/c2b/validation',
    'confirmation_url' => 'https://yourdomain.com/api/c2b/confirmation',
    'response_type' => 'Completed', // Completed | Canceled
]);

// PHP 8.0+
$response = Mpesa::registerC2BUrl(
    validationUrl: 'https://yourdomain.com/api/c2b/validation',
    confirmationUrl: 'https://yourdomain.com/api/c2b/confirmation',
    responseType: 'Completed'
);
```

---

## Custom Authentication

Override credentials per request (useful for multi-tenant apps):

```php
// PHP 7.4+ (include 'auth' key in array)
$response = Mpesa::stkPush([
    'phone' => '254712345678',
    'amount' => '100',
    'account_reference' => 'INV-123',
    'transaction_desc' => 'Payment',
    'auth' => [
        'consumer_key' => 'different_key',
        'consumer_secret' => 'different_secret',
        'shortcode' => '123456',
        'passkey' => 'different_passkey',
    ],
]);

// PHP 8.0+
$response = Mpesa::stkPush(
    phone: '254712345678',
    amount: '100',
    accountReference: 'INV-123',
    transactionDesc: 'Payment',
    auth: [
        'consumer_key' => 'different_key',
        'consumer_secret' => 'different_secret',
    ]
);
```

---

## Parameter Reference

### Key Naming Convention

Both `camelCase` and `snake_case` keys are supported in arrays:

| camelCase | snake_case | Description |
|-----------|------------|-------------|
| `accountReference` | `account_reference` | Payment reference |
| `transactionDesc` | `transaction_desc` | Transaction description |
| `checkoutRequestId` | `checkout_request_id` | STK query ID |
| `partyB` | `party_b` | Recipient shortcode/phone |
| `partyA` | `party_a` | Sender shortcode |
| `commandId` | `command_id` | B2C command type |
| `identifierType` | `identifier_type` | Balance identifier |
| `originatorConversationId` | `originator_conversation_id` | Pochi conversation ID |
| `validationUrl` | `validation_url` | C2B validation URL |
| `confirmationUrl` | `confirmation_url