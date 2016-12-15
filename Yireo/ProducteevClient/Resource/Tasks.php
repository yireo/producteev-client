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

        $this->update($taskId, $taskData);

        if (isset($taskData['responsibles'])) {
            foreach ($taskData['responsibles'] as $userId) {
                $this->addResponsible($taskId, $userId);
            }
        }

        return $taskId;
    }

    public function update($taskId, $taskData)
    {
        return $this->client->put('tasks/' . $taskId, ['task' => $taskData]);
    }

    public function addResponsible($taskId, $userId)
    {
        if (is_array($userId) && isset($userId['id'])) {
            $userId = $userId['id'];
        }

        return $this->client->put('tasks/' . $taskId . '/responsibles/' . $userId);
    }

    public function removeResponsible($taskId, $userId)
    {
        if (is_array($userId) && isset($userId['id'])) {
            $userId = $userId['id'];
        }

        return $this->client->delete('tasks/' . $taskId . '/responsibles/' . $userId);
    }
}