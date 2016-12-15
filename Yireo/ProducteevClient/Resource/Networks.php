<?php
namespace Yireo\ProducteevClient\Resource;

use InvalidArgumentException;

class Networks
{
    private $client;

    public function __construct(\Yireo\ProducteevClient\Client $client)
    {
        $this->client = $client;
    }

    public function items()
    {
        $data = $this->client->get('networks');

        return $data['networks'];
    }

    public function getFirstNetwork()
    {
        $networks = $this->client->getResource('networks')->items();
        return $networks[0];
    }
}