# Safaricom M-Pesa Daraja API Laravel Package

[![Issues](https://img.shields.io/github/issues/kelvinthiongo/mpesa?style=flat-square)](https://github.com/kelvinthiongo/mpesa/issues)
[![Stars](https://img.shields.io/github/stars/kelvinthiongo/mpesa?style=flat-square)](https://github.com/kelvinthiongo/mpesa/stargazers)

A modern, clean, and comprehensive Laravel package for integrating with Safaricom's M-Pesa Daraja API. Supports STK Push, B2C, B2B, Balance Inquiry, and C2B registration.

## Features

- ✅ **STK Push (M-Pesa Express)** - Prompt customers to pay via M-Pesa
- ✅ **B2C Payments** - Send money to customers (SalaryPayment, BusinessPayment, PromotionPayment)
- ✅ **B2B Payments** - Pay bills or buy goods from businesses (PayBill, BuyGoods, PayToPochi)
- ✅ **Account Balance** - Check your M-Pesa account balance
- ✅ **C2B URL Registration** - Register validation and confirmation URLs
- ✅ **Token Caching** - Automatic access token management with caching
- ✅ **Custom Authentication** - Override credentials per transaction
- ✅ **Comprehensive Validation** - Built-in parameter validation
- ✅ **Exception Handling** - Clear error messages
- ✅ **PHP 7.4+ Support** - Modern PHP with backward compatibility

## Installation

Install via Composer:
```bash
composer require tfs/mpesa
```

Publish the configuration file:
```bash
php artisan vendor:publish --provider="TFS\Mpesa\MpesaServiceProvider"
```

## Configuration

Add the following to your `.env` file:
```env
# Mode: sandbox or live
MPESA_MODE=sandbox

# Callback URLs
MPESA_CALLBACK_URL=https://yourdomain.com/api/mpesa/callback
MPESA_BALANCE_CALLBACK_URL=https://yourdomain.com/api/mpesa/balance

# C2B (Customer to Business) - For receiving payments
MPESA_CONSUMER_KEY=your_consumer_key
MPESA_CONSUMER_SECRET=your_consumer_secret
MPESA_SHORTCODE=your_shortcode
MPESA_PASSKEY=your_passkey

# B2C/B2B (Business to Customer/Business) - For sending money
MPESA_B2C_CONSUMER_KEY=your_b2c_consumer_key
MPESA_B2C_CONSUMER_SECRET=your_b2c_consumer_secret
MPESA_B2C_SHORTCODE=your_b2c_shortcode
MPESA_INITIATOR_NAME=your_initiator_name
MPESA_INITIATOR_PASSWORD=your_initiator_password

# Sandbox credentials (for testing)
MPESA_SANDBOX_CONSUMER_KEY=your_sandbox_key
MPESA_SANDBOX_CONSUMER_SECRET=your_sandbox_secret
MPESA_SANDBOX_SHORTCODE=174379
MPESA_SANDBOX_PASSKEY=bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919
MPESA_SANDBOX_B2C_SHORTCODE=600000
MPESA_SANDBOX_INITIATOR_NAME=testapi
MPESA_SANDBOX_INITIATOR_PASSWORD=Safaricom999!*!
```

## Usage

### 1. STK Push (M-Pesa Express)

Prompt a customer to enter their M-Pesa PIN and complete payment:
```php
use TFS\Mpesa\Mpesa;

// Basic usage
$response = Mpesa::stkPush(
    phone: '254712345678',
    amount: '100',
    accountReference: 'INV-12345',
    transactionDesc: 'Payment for order #12345'
);

// With custom callback
$response = Mpesa::stkPush(
    phone: '254712345678',
    amount: '100',
    accountReference: 'INV-12345',
    transactionDesc: 'Payment for order #12345',
    callback: 'https://yourdomain.com/custom/callback'
);

// With custom auth credentials
$response = Mpesa::stkPush(
    phone: '254712345678',
    amount: '100',
    accountReference: 'INV-12345',
    transactionDesc: 'Payment for order #12345',
    auth: [
        'consumer_key' => 'different_key',
        'consumer_secret' => 'different_secret',
        'shortcode' => '123456',
        'passkey' => 'different_passkey'
    ]
);
```

#### Query STK Push Status
```php
$response = Mpesa::stkQuery('ws_CO_12345678');
```

### 2. B2C (Business to Customer) Payments

Send money to a customer's M-Pesa account:
```php
use TFS\Mpesa\Mpesa;

// Salary payment
$response = Mpesa::b2c(
    phone: '254712345678',
    amount: '5000',
    occasion: 'Salary',
    remarks: 'Monthly salary payment',
    commandId: 'SalaryPayment'
);

// Business payment
$response = Mpesa::b2c(
    phone: '254712345678',
    amount: '1000',
    occasion: 'Refund',
    remarks: 'Order refund',
    commandId: 'BusinessPayment'
);

// Promotional payment
$response = Mpesa::b2c(
    phone: '254712345678',
    amount: '500',
    occasion: 'Promotion',
    remarks: 'Winner bonus',
    commandId: 'PromotionPayment'
);

// With custom auth
$response = Mpesa::b2c(
    phone: '254712345678',
    amount: '1000',
    occasion: 'Payment',
    remarks: 'Payment description',
    auth: [
        'b2c_consumer_key' => 'custom_key',
        'b2c_consumer_secret' => 'custom_secret',
        'b2c_shortcode' => '600000',
        'initiator_name' => 'custom_initiator',
        'initiator_password' => 'custom_password'
    ]
);
```

### 3. B2B (Business to Business) Payments

#### Pay Bill

Pay a bill directly from your business account:
```php
$response = Mpesa::b2bPayBill(
    partyB: '888880',  // Recipient paybill number
    amount: '1000',
    accountReference: 'ACC-12345',
    remarks: 'Payment for services'
);

// On behalf of a customer (requester)
$response = Mpesa::b2bPayBill(
    partyB: '888880',
    amount: '1000',
    accountReference: 'ACC-12345',
    remarks: 'Payment for services',
    requester: '254712345678'
);
```

#### Buy Goods

Pay to a till number or merchant:
```php
$response = Mpesa::b2bBuyGoods(
    partyB: '888880',  // Recipient till number
    amount: '500',
    accountReference: 'ORDER-789',
    remarks: 'Payment for goods'
);
```

#### Pay to Pochi (Business Wallet)

Send money to a business's Pochi la Biashara (business wallet):
```php
$response = Mpesa::b2Pochi(
    partyB: '254712345678',  // Business phone number
    amount: '1000',
    occasion: 'Payment',
    remarks: 'Payment to business wallet',
    originatorConversationId: 'CONV-' . time()
);
```

### 4. Account Balance

Check your M-Pesa account balance:
```php
use TFS\Mpesa\Mpesa;

$response = Mpesa::balance(
    partyA: '600000',  // Your shortcode
    remarks: 'Balance inquiry',
    identifierType: 4  // 1=MSISDN, 2=Till, 4=Shortcode
);

// With custom callback
$response = Mpesa::balance(
    partyA: '600000',
    remarks: 'Balance inquiry',
    identifierType: 4,
    callback: 'https://yourdomain.com/balance/callback'
);
```

### 5. C2B URL Registration

Register your validation and confirmation URLs for C2B:
```php
use TFS\Mpesa\Mpesa;

$response = Mpesa::registerC2BUrl(
    validationUrl: 'https://yourdomain.com/api/c2b/validation',
    confirmationUrl: 'https://yourdomain.com/api/c2b/confirmation',
    responseType: 'Completed',  // or 'Canceled'
    shortCode: '174379'
);
```

## Custom Authentication

All methods support custom authentication via the `auth` parameter:
```php
$customAuth = [
    'consumer_key' => 'your_key',
    'consumer_secret' => 'your_secret',
    'shortcode' => '123456',
    'passkey' => 'your_passkey',
    'b2c_consumer_key' => 'your_b2c_key',
    'b2c_consumer_secret' => 'your_b2c_secret',
    'b2c_shortcode' => '600000',
    'initiator_name' => 'your_initiator',
    'initiator_password' => 'your_password'
];

$response = Mpesa::stkPush(
    phone: '254712345678',
    amount: '100',
    accountReference: 'REF-123',
    transactionDesc: 'Payment',
    auth: $customAuth
);
```

## Response Handling

All methods return an array with the API response:
```php
$response = Mpesa::stkPush(...);

// Successful response
[
    'MerchantRequestID' => '29115-34620561-1',
    'CheckoutRequestID' => 'ws_CO_191220191020363925',
    'ResponseCode' => '0',
    'ResponseDescription' => 'Success. Request accepted for processing',
    'CustomerMessage' => 'Success. Request accepted for processing'
]

// Error response
[
    'requestId' => '11728-2929992-1',
    'errorCode' => '400.002.02',
    'errorMessage' => 'Bad Request - Invalid Amount'
]
```

## Exception Handling

The package throws two types of exceptions:
```php
use TFS\Mpesa\Exceptions\{MpesaException, ValidationException};

try {
    $response = Mpesa::stkPush(...);
} catch (ValidationException $e) {
    // Validation errors (invalid parameters)
    Log::error('Validation error: ' . $e->getMessage());
} catch (MpesaException $e) {
    // API errors
    Log::error('M-Pesa error: ' . $e->getMessage());
}
```

## Testing

The package automatically uses sandbox credentials when `MPESA_MODE=sandbox`:
```env
MPESA_MODE=sandbox
```

### Test Credentials (Sandbox)
```
Consumer Key: jIJBAV99OQsznKIdjHGrM2NeyN7JC8BR
Consumer Secret: qoCqvvcE9qHIrOH3
Shortcode: 174379
Passkey: bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919
B2C Shortcode: 600000
Initiator Name: testapi
Initiator Password: Safaricom999!*!
Test Phone: 254708374149
```

## Callback Handling

Create routes to handle M-Pesa callbacks:
```php
// routes/api.php
Route::post('/mpesa/callback', [MpesaController::class, 'handleCallback']);
Route::post('/mpesa/timeout', [MpesaController::class, 'handleTimeout']);
```
```php
// app/Http/Controllers/MpesaController.php
public function handleCallback(Request $request)
{
    $result = $request->all();
    
    // Process the callback
    Log::info('M-Pesa callback:', $result);
    
    // Return success response
    return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Success']);
}
```

## Changelog

### Version 2.0.0
- Complete package refactor with service-based architecture
- Added comprehensive validation
- Implemented token caching
- Added support for custom authentication
- Improved error handling with custom exceptions
- Added B2B BuyGoods and PayToPochi support
- Better documentation

## Contributing

Contributions are welcome! Please submit pull requests to the [GitHub repository](https://github.com/kelvinthiongo/mpesa).

## License

This package is open-source software licensed under the MIT license.

## Support

For issues and questions, please use the [GitHub issue tracker](https://github.com/kelvinthiongo/mpesa/issues).

## Credits

Developed by [Kelvin Thiongo](https://github.com/kelvinthiongo)