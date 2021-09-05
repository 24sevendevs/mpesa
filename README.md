# Safaricom Mpesa Daraja API Laravel Package

[![Issues](https://img.shields.io/github/issues/kelvinthiongo/mpesa?style=flat-square)](https://github.com/kelvinthiongo/mpesa/issues)
[![Stars](https://img.shields.io/github/stars/kelvinthiongo/mpesa?style=flat-square)](https://github.com/kelvinthiongo/mpesa/stargazers)

## This package will enable you to consume Safaricom Mpesa Daraja API with a lot of ease. It is meant for Laravel developers.

## Installing Laravel Mpesa Daraja API package

The recommended way to install the laravel package for Safaricom Mpesa Daraja API is through
[Composer](https://getcomposer.org/).

```bash
composer require tfs/mpesa
```

Run vendor:publish artisan command

```bash
php artisan vendor:publish --provider="TFS\Mpesa\MpesaServiceProvider"
```

After publishing you will find config/mpesa.php config file. You can now adjust the configurations appropriately. Additionally, add the configurations to your env for security purposes.

Add the following files to your .env

```env

MPESA_CONSUMER_KEY=
MPESA_CONSUMER_SECRET=
MPESA_SHORTCODE=
MPESA_PASSKEY=
MPESA_CALLBACK_URL=

MPESA_INITIATOR_NAME=
MPESA_INITIATOR_PASSWORD=
MPESA_B2C_CONSUMER_KEY=
MPESA_B2C_CONSUMER_SECRET=
MPESA_B2C_SHORTCODE=

MPESA_MODE=sandbox
```

## Usage

#### Mpesa Express

```php
use TFS\Mpesa\Mpesa;

...
$response = Mpesa::mpesa_express($phone, $amount, $AccountReference, $TransactionDesc, $callback = null);
...

eg.

$response = Mpesa::mpesa_express("254723077827", 1, "AccountReference", "TransactionDesc");
```

#### B2C

```php
use TFS\Mpesa\Mpesa;

...
$response = Mpesa::b2c($phone, $amount, $occassion, $remarks, $callback = null, $command_id = null);
...

eg.

$response = Mpesa::b2c("254708374149", 10, "Test occassion", "Test remarks");
```

#### Balance

```php
use TFS\Mpesa\Mpesa;

...

$result = Mpesa::balance($partyA, $remarks, $callback, $identifierType, $consumer_key, $consumer_secret, $initiator_name, $initiator_password, $queueTimeOutURL = null);
...

eg.

$partyA = config('mpesa.live.b2c_shortcode');
$remarks = "Balance";
$callback = config('mpesa.balance_callback_url');
$identifierType = 4; //1 – MSISDN, 2 – Till Number, 4 – Organization short code!
$consumer_key = config("mpesa." . config('mpesa.mode') . ".b2c_consumer_key");
$consumer_secret = config("mpesa." . config('mpesa.mode') . ".b2c_consumer_secret");
$consumer_key = config("mpesa." . config('mpesa.mode') . ".b2c_consumer_key");
$initiator_name = config("mpesa." . config('mpesa.mode') . ".initiator_name");
$initiator_password = config("mpesa." . config('mpesa.mode') . ".initiator_password");

$result = Mpesa::balance($partyA, $remarks, $callback, $identifierType, $consumer_key, $consumer_secret, $initiator_name, $initiator_password);
```

#### C2B Rigister URL

```php
use TFS\Mpesa\Mpesa;

...

$result = Mpesa::c2b_register_url($ValidationURL, $ConfirmationURL, $ResponseType, $ShortCode, $consumer_key = null, $consumer_secret = null)
...

eg.

$ValidationURL = "https://24seven.co.ke/api/payments/c2b-validation";
$ConfirmationURL = "https://24seven.co.ke/api/payments/c2b-confirmation";
$ResponseType = "Completed";//Canceled
$ShortCode = config("mpesa." . config('mpesa.mode') . ".shortcode");

$result = Mpesa::balance($partyA, $remarks, $callback, $identifierType, $consumer_key, $consumer_secret, $initiator_name, $initiator_password, $queueTimeOutURL = null);
```
