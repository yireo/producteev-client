<?php
namespace Yireo\ProducteevClient;

use GuzzleHttp\Client as HttpClient;
use Exception as Exception;

class Client
{
    const AUTH_URL = 'https://www.producteev.com/oauth/v2/';

    const API_URL = 'https://www.producteev.com/api/';

    private $clientId = '';

    private $clientSecret = '';

    private $redirectUrl = '';

    private $accessToken = '';

    private $refreshToken = '';

    private $accessTokenExpirationTime = 0;

    private $authenticationCode = '';

    private $cookie;

    public function __construct($clientId = '', $clientSecret = '', $redirectUrl = '')
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUrl = $redirectUrl;
    }

    /**
     * @return string
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * @param string $clientId
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;
    }

    /**
     * @return string
     */
    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    /**
     * @param string $clientSecret
     */
    public function setClientSecret($clientSecret)
    {
        $this->clientSecret = $clientSecret;
    }

    /**
     * @return string
     */
    public function getRedirectUrl()
    {
        return $this->redirectUrl;
    }

    /**
     * @param string $redirectUrl
     */
    public function setRedirectUrl($redirectUrl)
    {
        $this->redirectUrl = $redirectUrl;
    }

    /**
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @param string $accessToken
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    /**
     * @return bool
     */
    public function isAccessTokenValid()
    {
        if (empty($this->accessToken)) {
            return false;
        }

        if (strlen($this->accessToken) < 10) {
            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    public function getAuthenticationCode()
    {
        return $this->authenticationCode;
    }

    /**
     * @param string $authenticationCode
     */
    public function setAuthenticationCode($authenticationCode)
    {
        $this->authenticationCode = $authenticationCode;
    }

    public function authenticate($debug = false)
    {
        $redirectUrl = urlencode($this->redirectUrl);
        $clientId = $this->clientId;

        if (empty($clientId)) {
            throw new Exception('Empty client ID');
        }

        if (empty($redirectUrl)) {
            throw new Exception('Empty redirect URL');
        }

        $authenticateUrl = self::AUTH_URL . 'auth?client_id=' . $clientId . '&response_type=code&redirect_uri=' . $redirectUrl;

        if ($debug) {
            echo '<a href="' . $authenticateUrl . '">Producteev authentication via OAuth2</a>';
            exit;
        }

        header('Location: ' . $authenticateUrl);
        exit;
    }

    public function retrieveAccessTokenToCookie()
    {
        $redirectUrl = urlencode($this->redirectUrl);
        $clientId = $this->clientId;
        $clientSecret = $this->clientSecret;
        $authenticationCode = $this->authenticationCode;

        if (empty($clientId)) {
            throw new Exception('Empty client ID');
        }

        if (empty($clientSecret)) {
            throw new Exception('Empty client secret');
        }

        if (empty($redirectUrl)) {
            throw new Exception('Empty redirect URL');
        }

        if (empty($authenticationCode)) {
            throw new Exception('Empty authentication code');
        }

        $tokenUrl = self::AUTH_URL . 'token?client_id=' . $clientId . '&client_secret=' . $clientSecret . '&grant_type=authorization_code&redirect_uri=' . $redirectUrl . '&code=' . $authenticationCode;;

        $httpClient = new HttpClient();

        try {
            $response = $httpClient->request('get', $tokenUrl);
        } catch (Exception $e) {
            throw new Exception('Token request failed. Please re-authenticate');
        }

        $responseCode = $response->getStatusCode();
        $responseBody = $response->getBody();

        if ($responseCode !== 200) {
            throw new Exception(sprintf('Unexpected status code %s', $responseCode));
        }

        if (empty($responseBody)) {
            throw new Exception(sprintf('Empty response from token URL %s', $tokenUrl));
        }

        $data = json_decode($responseBody, true);

        if (empty($data) || !is_array($data)) {
            throw new Exception(sprintf('No JSON response: %s', $responseBody));
        }

        if (isset($data['error']) && isset($data['error_description'])) {
            throw new Exception(sprintf('Error %s: %s', $data['error'], $data['error_description']));
        }

        $this->accessToken = $data['access_token'];
        $this->accessTokenExpirationTime = time() + (int)$data['expires_in'] - 120;
        $this->refreshToken = $data['refresh_token'];

        $cookieData = [];
        $cookieData['access_token'] = $this->accessToken;
        $cookieData['refresh_token'] = $this->refreshToken;
        $cookie = new Cookie($this, $this->accessTokenExpirationTime);
        $cookie->setData($cookieData);
        $cookie->save();
    }

    public function retrieveAccessTokenFromCookie()
    {
        $cookie = new Cookie($this);
        $cookieData = $cookie->getData();

        if (isset($cookieData['access_token'])) {
            $this->accessToken = $cookieData['access_token'];
        }

        if (isset($cookieData['refresh_token'])) {
            $this->refreshToken = $cookieData['refresh_token'];
        }
    }

    public function request($resource, $requestType = 'GET', $options = [])
    {
        $accessToken = $this->getAccessToken();

        if (empty($accessToken)) {
            throw new Exception('Empty access token');
        }

        $apiUrl = self::API_URL . $resource;
        $options['headers']['Authorization'] = 'Bearer ' . $accessToken;

        $httpClient = new HttpClient();

        try {
            $response = $httpClient->request($requestType, $apiUrl, $options);
        } catch (\GuzzleHttp\Exception\ClientException $exception) {
            $response = $exception->getResponse();
            $responseCode = $response->getStatusCode();
            $responseBody = $response->getBody();
            $responseData = json_decode($responseBody, true);

            $message = 'Producteev responds with HTTP Status ' . $responseCode . ' ['.$resource.']';

            if (isset($responseData['error_description'])) {
                $message .= ': ' . $responseData['error_description'];
            }

            $message .= ' Data sent: ' . var_export($options, true);

            throw new Exception($message);
        }


        $responseCode = $response->getStatusCode();
        $responseBody = $response->getBody();

        if (!in_array($responseCode, [200, 201, 204])) {
            throw new Exception('Unexpected server error: ' . $responseCode);
        }

        if ($responseCode == 204) {
            return [];
        }

        if (empty($responseBody)) {
            throw new Exception(sprintf('Empty response from token URL %s', $apiUrl));
        }

        $responseData = json_decode($responseBody, true);

        if (empty($responseData)) {
            throw new Exception(sprintf('No JSON response: %s', $responseBody));
        }

        return $responseData;
    }

    public function get($resource, $data = [])
    {
        if (!empty($data)) {
            return $this->request($resource, 'GET', ['query' => $data]);
        }
        return $this->request($resource, 'GET');
    }

    public function post($resource, $data)
    {
        return $this->request($resource, 'POST', ['body' => json_encode($data)]);
    }

    public function put($resource, $data = [])
    {
        if (!empty($data)) {
            return $this->request($resource, 'PUT', ['body' => json_encode($data)]);
        }
        return $this->request($resource, 'PUT');
    }

    public function delete($resource, $data = [])
    {
        if (!empty($data)) {
            return $this->request($resource, 'DELETE', ['body' => json_encode($data)]);
        }
        return $this->request($resource, 'DELETE');
    }

    public function getResource($resourceName)
    {
        $resourceClass = '\\Yireo\\ProducteevClient\\Resource\\' . ucfirst($resourceName);
        return new $resourceClass($this);
    }
}