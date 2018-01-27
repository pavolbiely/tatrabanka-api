# Tatra Banka - Open banking TB
[![Build Status](https://travis-ci.org/pavolbiely/tatrabanka-api.svg?branch=master)](https://travis-ci.org/pavolbiely/tatrabanka-api)
[![Coverage Status](https://coveralls.io/repos/github/pavolbiely/tatrabanka-api/badge.svg?branch=master)](https://coveralls.io/github/pavolbiely/tatrabanka-api?branch=master)
[![Donate](https://img.shields.io/badge/Donate-PayPal-green.svg)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=BHZKXCWAK2NNS)

PHP REST API Client for [Tatra Banka](https://www.tatrabanka.sk/)'s [Open Banking TB](https://www.tatrabanka.sk/sk/personal/ucet-platby/elektronicke-bankovnictvo/openbankingtb.html).

Sign up at [developer.tatrabanka.sk](https://developer.tatrabanka.sk) to get access to the API.

## Installation

Use composer to install this package.

## Example of usage

### Accounts API

Ask for the OAuth2 authorization
```php
use TatraBankaApi\Accounts;

$clientId = '';
$clientSecret = '';
$redirectUri = '';

$tb = new Accounts($clientId, $clientSecret, $redirectUri);
$tb->useSandbox(true);
header('Location: ' . $tb->getAuthorizationUrl());
```

Exchange the OAuth2 authorization code for an access token
```php
use TatraBankaApi\Accounts;
use TatraBankaApi\TatraBankaApiException;

$clientId = '';
$clientSecret = '';
$redirectUri = '';

try {
    $tb = new Accounts($clientId, $clientSecret, $redirectUri);
    $tb->useSandbox(true);
    $tb->requestAccessToken($_GET['code']); // using authorization_code grand type
} catch (TatraBankaApiException $e) {
    // ...
}
```

General usage
```php
use TatraBankaApi\Accounts;
use TatraBankaApi\TatraBankaApiException;

$clientId = '';
$clientSecret = '';
$redirectUri = '';

try {
    $tb = new Accounts($clientId, $clientSecret, $redirectUri);
    $tb->useSandbox(true);

    if ($tb->isAuthorized()) {
        // The operation provides the relevant data about bank customer's accounts in form of a list.
        print_r($tb->getAccounts());

        // The operation provides the relevant data from a bank customer's account identified by IBAN.
        print_r($tb->postAccountInfo('SK0511000000002600000054'));

        // The list of financial transactions perfomed on a customer's bank account withing a date period.
        print_r($tb->postTransactions('SK0511000000002600000054', Accounts::STATUS_ALL, new \DateTime('-1 month'),  new \DateTime('now'), 1, 10));
    }
} catch (TatraBankaApiException $e) {
    // ...
}
```

## Payments API
Get the OAuth2 access token and prepare the payment instructions
```php
use TatraBankaApi\Accounts;
use TatraBankaApi\TatraBankaApiException;

$clientId = '';
$clientSecret = '';
$redirectUri = '';

try {
    $tb = new Accounts($clientId, $clientSecret, $redirectUri);
    $tb->useSandbox(true);
    $tb->requestAccessToken(); // using client_credentials grand type
    $response = $tb->postPaymentSba(md5(uniqid('', true)), $debtor, $creditor, $amount, new \DateTime('tomorrow'), new \DateTime('now'), '/VS123/SS456/KS0308', 'Test');
    $authUrl = $tb->getAuthorizationUrl(['orderId' => $response->orderId]);
    header('Location: ' . $authUrl);
    exit;
} catch (TatraBankaApiException $e) {
    // ...
}
```

Get the OAuth2 access token and prepare the payment instructions
```php
use TatraBankaApi\Accounts;
use TatraBankaApi\TatraBankaApiException;

$clientId = '';
$clientSecret = '';
$redirectUri = '';

try {
    $tb = new Accounts($clientId, $clientSecret, $redirectUri);
    $tb->useSandbox(true);
    $tb->requestAccessToken(); // using client_credentials grand type
    $response = $tb->postPaymentSba(md5(uniqid('', true)), $debtor, $creditor, $amount, new \DateTime('tomorrow'), new \DateTime('now'), '/VS123/SS456/KS0308', 'Test');
    $authUrl = $tb->getAuthorizationUrl(['orderId' => $response->orderId]);
    header('Location: ' . $authUrl);
    exit;
} catch (TatraBankaApiException $e) {
    // ...
}
```

Exchange the OAuth2 authorization code for an access token and submit the payment
```php
use TatraBankaApi\Accounts;
use TatraBankaApi\TatraBankaApiException;

$clientId = '';
$clientSecret = '';
$redirectUri = '';

try {
    $tb = new Accounts($clientId, $clientSecret, $redirectUri);
    $tb->useSandbox(true);
    $tb->requestAccessToken($_GET['code']); // using authorization_code grand type
    print_r($tb->postPaymentSubmission());

} catch (TatraBankaApiException $e) {
    // ...
}
```

Get payment status
```php
use TatraBankaApi\Accounts;
use TatraBankaApi\TatraBankaApiException;

$clientId = '';
$clientSecret = '';
$redirectUri = '';
$orderId = '';

try {
    $tb = new Accounts($clientId, $clientSecret, $redirectUri);
    $tb->useSandbox(true);
    $tb->requestAccessToken(); // using client_credentials grand type
    print_r($tb->getPaymentStatus(['orderId' => $orderId]));

} catch (TatraBankaApiException $e) {
    // ...
}
```

## How to run tests?
Tests are build with [Nette Tester](https://tester.nette.org/). You can run it like this:
```bash
php -f tester ./ -c php.ini-mac --coverage coverage.html --coverage-src ../src
```

## Minimum requirements
- PHP 7.1+
- php-curl

## License
MIT License (c) Pavol Biely

Read the provided LICENSE file for details.
