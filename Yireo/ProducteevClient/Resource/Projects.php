<?php
namespace Yireo\ProducteevClient\Resource;

use InvalidArgumentException;

// @todo: Missing overview of projects

class Projects
{
    private $client;

    public function __construct(\Yireo\ProducteevClient\Client $client)
    {
        $this->client = $client;
    }

    public function create($projectData)
    {
        if (!is_array($projectData)) {
            throw new InvalidArgumentException('Input argument should be an array');
        }

        if (!isset($projectData['title'])) {
            throw new InvalidArgumentException('Title is required');
        }

        // By default, use the first network availablel
        if (empty($projectData['network']['id'])) {
            $network = $this->client->getResource('networks')->getFirstNetwork();
            $projectData['network']['id'] = $network['id'];
        }

        if (empty($projectData['network']['id'])) {
            throw new InvalidArgumentException('Network is required');
        }

        $data = $this->client->post('projects', ['project' => $projectData]);

        return $data['project']['id'];
    }
}