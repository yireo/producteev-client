<?php
namespace Yireo\ProducteevClient;

use Yireo\ProducteevClient\Client;

/**
 * Class Resource
 *
 * @package Yireo\ProducteevClient
 */
abstract class Resource
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * Resource constructor.
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }
}