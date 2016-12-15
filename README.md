# Producteev Client
PHP client for the [Producteev](https://www.producteev.com/) API.

## Status
Alpha (not ready for production). Because of this, this library is not available on Packagist (yet).

## Installation
This client is installed via composer:

    composer config repositories.yireo-producteev vcs git@github.com:yireo/producteev-client.git
    composer require yireo/producteev-client:dev-master

## Usage
First of all, see the documentation of Producteev on the usage of their API:
https://www.producteev.com/api/doc/#Introduction

Read their instructions on how to create a new app. Write down the client ID and client secret of this app. In the example below
these are the `$clientId` and `$clientSecret` variables.

```php
require __DIR__ . '/vendor/autoload.php';

$client = new \Yireo\ProducteevClient\Client($clientId, $clientSecret);
$client->retrieveAccessTokenFromCookie();
$redirectUrl = 'http://addressbook.yireo.dev/producteev_test.php';
$client->setRedirectUrl($redirectUrl);

if (!empty($_REQUEST['code'])) {
    $client->setAuthenticationCode($_REQUEST['code']);
    $client->retrieveAccessTokenToCookie();

    header('Location: ' . $redirectUrl);
    exit;
}

if($client->isAccessTokenValid() === false) {
    $client->authenticate();
}

echo '<pre>';
print_r($client->getCurrentUser());
echo '</pre>';
```
