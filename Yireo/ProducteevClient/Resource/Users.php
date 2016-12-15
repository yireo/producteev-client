<?php
namespace Yireo\ProducteevClient\Resource;

use InvalidArgumentException;

// @todo: Missing overview of users

class Users
{
    private $client;

    public function __construct(\Yireo\ProducteevClient\Client $client)
    {
        $this->client = $client;
    }

    public function me()
    {
        $data = $this->client->get('users/me');

        return $data['user'];
    }

    public function getMyDefaultProject()
    {
        $data = $this->client->get('users/me/default_project');

        return $data['project'];
    }
}