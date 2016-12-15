<?php
namespace Yireo\ProducteevClient\Resource;

use InvalidArgumentException;

class Tasks
{
    private $client;

    public function __construct(\Yireo\ProducteevClient\Client $client)
    {
        $this->client = $client;
    }

    public function create($taskData)
    {
        if (!is_array($taskData)) {
            throw new InvalidArgumentException('Input argument should be an array');
        }

        if (!isset($taskData['title'])) {
            throw new InvalidArgumentException('Title is required');
        }

        if (empty($taskData['project']['id'])) {
            throw new InvalidArgumentException('Network is required');
        }

        $data = $this->client->post('tasks', ['task' => $taskData]);
        $taskId = $data['task']['id'];

        $data = $this->client->put('tasks/' . $taskId, ['task' => $taskData]);

        return $taskId;
    }
}