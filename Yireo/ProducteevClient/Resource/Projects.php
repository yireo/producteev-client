<?php
namespace Yireo\ProducteevClient\Resource;

use InvalidArgumentException;
use \Yireo\ProducteevClient\Resource;

class Projects extends Resource
{
    protected $networkId = 0;

    public function items()
    {
        $data = $this->client->get('networks/' . $this->getNetworkId() . '/projects');

        return $data['projects'];
    }

    public function search($searchPhrase)
    {
        $projects = $this->items();
        $matchProjects = [];

        foreach ($projects as $project) {
            if (stristr($project['title'], $searchPhrase)) {
                $matchProjects[] = $project;
            }
        }

        return $matchProjects;
    }

    public function create($projectData)
    {
        if (!is_array($projectData)) {
            throw new InvalidArgumentException('Input argument should be an array');
        }

        if (!isset($projectData['title'])) {
            throw new InvalidArgumentException('Title is required');
        }

        if (empty($projectData['network']['id'])) {
            $projectData['network']['id'] = $this->getNetworkId();
        }

        if (empty($projectData['network']['id'])) {
            throw new InvalidArgumentException('Network is required');
        }

        $data = $this->client->post('projects', ['project' => $projectData]);

        return $data['project']['id'];
    }

    /**
     * @return int
     */
    public function getNetworkId()
    {
        if (empty($this->networkId)) {
            $network = $this->client->getResource('networks')->getFirstNetwork();
            $this->networkId = $network['id'];
        }

        return $this->networkId;
    }

    /**
     * @param int $networkId
     */
    public function setNetworkId($networkId)
    {
        $this->networkId = $networkId;
    }
}