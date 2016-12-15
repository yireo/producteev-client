<?php
namespace Yireo\ProducteevClient;

use Yireo\AddressBook\Exception\InvalidArgument;
use Yireo\Common\String\VariableName;

class Client
{
    private $client_id = '';

    private $client_secret = '';

    private $base_url = 'https://www.producteev.com/oauth/v2/';

    private $returnType = '';

    private $params = [];

    private $token = '';

    private $dump;

    /**
     * Basically, you can either set it here if you only make occasional calls to the api, or (preferred method)
     * just set it directly in the class by modifying $client_id and $client_secret directly. That way you're not
     * setting it as part of every instantiation.
     *
     * @param string $key your api key
     * @param string $secret your api secret
     * @throws \Exception
     */
    public function __construct($key = '', $secret = '')
    {
        if ($this->client_id == null) {
            if ($key == '') {
                throw new \Exception('Producteev API Key not set');
            } else {
                $this->client_id = $key;
            }
        }
        if ($this->client_secret == null) {
            if ($secret == '') {
                throw new \Exception('Producteev API Secret not set');
            } else {
                $this->client_secret = $secret;
            }
        }
    }

    /**
     *
     * @param  string $type one of json |
     *
     * @return void
     * @throws \Exception
     */
    public function setReturnType($type)
    {
        if (in_array($type, array('json'))) {
            $this->returnType = $type;
        } else {
            throw new \Exception('Invalid request type');
        }
    }

    public function setParams($params)
    {
        foreach ($params as $name => $value) {
            $this->setParam($name, $value);
        }
    }

    /**
     *
     * @param $name string
     * @param $value mixed
     *
     * @return void
     */
    public function setParam($name, $value = '')
    {
        $this->params[$name] = $value;
    }

    public function getParam($key)
    {
        return $this->params[$key];
    }

    /**
     * Logs in
     *
     * @param  string $email
     * @param  string $password
     *
     * @return object
     */
    /*public function login($email, $password)
    {
        $this->setParam('email', $email);
        $this->setParam('password', $password);
        $d = $this->execute('users/login');
        $this->token = $d->login->token;
        return $d;
    }*/

    /**
     * Executes whatever action is passed to it.
     *
     * This method ensures that the url is properly formatted. It doesn't actually perform an direct operation on
     * the url, instead it will call $this->Curl() which will actually perform the request and deal with the
     * results.
     *
     * @param  string $action One of the supported actions from the api
     *
     * @return mixed An stdObject containing the results of your request
     */
    public function execute($action)
    {
        $url = $this->base_url . $action;
        $url .= ($this->returnType == null) ? '.json?' : '.' . $this->returnType . '?';
        $url .= $this->generateUrl();
        return $this->curl($url);
    }

    /**
     * At the moment this just performs the request and then returns the data (after storing the token if there
     * is one)
     *
     * @param  $url
     *
     * @return mixed
     * @throws \Exception
     */
    private function curl($url)
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,    // Shortcut for now
        ));
        $result = curl_exec($ch);
        $data = json_decode($result);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        switch (curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
            case 200:
                // Patch to ensure that the token is always stored
                if ($data->login != null && $data->login->token != null) {
                    $this->token = $data->login->token;
                }
                break;
            case 403:
                throw new \Exception('403 Forbidden ['.$url.']: ' . $data->error->message . ' = ' . var_export($this->dump, true));

            default:
                throw new \Exception('CURL error ['.$url.']: ' . ' HTTP Status ' . $httpCode . ' : ' . curl_error($ch));
        }
        
        curl_close($ch);
        $this->clearParams();
        return $data;
    }

    /**
     * Just deletes the params for now.
     *
     * @return void
     */
    public function clearParams()
    {
        $this->params = array();
    }

    /**
     * Generates the URL through concatination of params and persistent params. It generates the signature first
     * and then proceeds to mush all the parameters together.
     *
     * @return string
     */
    private function generateUrl()
    {
        $this->setParam('client_id', $this->client_id);

        if ($this->token != null) {
            $this->setParam('token', $this->token);
        }

        // Needs to be called after all params are set
        $this->generateSignature();
        $str = '';

        foreach ($this->params as $x => $d) {
            $str .= $x . '=' . urlencode($d) . '&';
        }

        return substr($str, 0, strlen($str) - 1);
    }

    /**
     * Essentially the same code that is present on the api website, this just constructs the signature
     *
     * @return void
     */
    public function generateSignature()
    {
        $str = '';
        ksort($this->params);   // THIS IS VITAL!

        foreach ($this->params as $k => $v) {
            if (is_string($v)) {
                $str .= "$k$v";
            }
        }

        $str .= $this->client_secret;
        $str = stripslashes($str);
        $this->setParam('api_sig', md5($str));
    }
}