<?php
namespace Yireo\ProducteevClient;

class Cookie
{
    private $name = '';

    private $expirationTime = 86400;

    private $data = [];

    public function __construct(Client $client, $expirationTime = 0)
    {
        $hash = md5($client->getClientId() . $client->getClientSecret());
        $this->name = 'producteev_' . $hash;

        if (!empty($expirationTime)) {
            $this->expirationTime = $expirationTime;
        }

        $this->retrieveFromCookie();
    }

    public function isValid()
    {
        $data = $this->getData();

        if (empty($data)) {
            return false;
        }

        return true;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        $this->data = $this->retrieveFromCookie();

        if ($this->data === false) {
            // @todo: How to handle this
        }

        return $this->data;
    }

    private function retrieveFromCookie()
    {
        if (!isset($_COOKIE[$this->name])) {
            return false;
        }

        return unserialize($_COOKIE[$this->name]);
    }

    public function save()
    {
        if (empty($this->data)) {
            return false;
        }

        setcookie($this->name, serialize($this->data), $this->expirationTime);
        return true;
    }
}