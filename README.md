# Producteev Client
*PHP client for the [Producteev](https://www.producteev.com/) API.*

This PHP client makes use of the Producteev API, authenticating using OAuth2 and depends on Guzzle.

## Status
Alpha (not ready for production). Because of this, this library is not available on Packagist (yet).

APIs tested:
- `users/me`

## Installation
This client is installed via composer:

    composer config repositories.yireo-producteev vcs git@github.com:yireo/producteev-client.git
    composer require yireo/producteev-client:dev-master

## Usage
First of all, see the documentation of Producteev on the usage of their API:
https://www.producteev.com/api/doc/#Introduction

Read their instructions on how to create a new app. Write down the client ID and client secret of this app. In the example below these are the `$clientId` and `$clientSecret` variables.

Now, setup a webpage, add the code below, and access it. In the example, the URL will be `http://example.com`:

```php
// Include all composer packages
require __DIR__ . '/vendor/autoload.php';

// Initialize the client with its ID and secret
$client = new \Yireo\ProducteevClient\Client($clientId, $clientSecret);

// Retrieve a current access token from cookie, if there
$client->retrieveAccessTokenFromCookie();

// Set the redirect URL
$redirectUrl = 'http://example.com/';
$client->setRedirectUrl($redirectUrl);

// When authenticating via OAuth2, Producteev will redirect back to us with a "code" set
if (!empty($_REQUEST['code'])) {
    $client->setAuthenticationCode($_REQUEST['code']);
    $client->retrieveAccessTokenToCookie();

    // Redirect to our same page again, removing the "code" part
    header('Location: ' . $redirectUrl);
    exit;
}

// When there is no valid access token, this will initiate OAuth2 authentication including a redirect to the Producteev webpage
if($client->isAccessTokenValid() === false) {
    $client->authenticate();
}

// Example call
echo '<pre>';
print_r($client->getCurrentUser());
echo '</pre>';
```

### Cookies
The access token is in this code sample stored in a cookie in the browser. Alternative storage can be used if you prefer.