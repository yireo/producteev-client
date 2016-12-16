<?php
namespace Yireo\ProducteevClient\Resource;

/**
 *
 */
use InvalidArgumentException;

/**
 * Class Tasks
 *
 * @package Yireo\ProducteevClient\Resource
 */
class Tasks
{
    /**
     * @var \Yireo\ProducteevClient\Client
     */
    private $client;

    /**
     * Tasks constructor.
     *
     * @param \Yireo\ProducteevClient\Client $client
     */
    public function __construct(\Yireo\ProducteevClient\Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param $taskData
     *
     * @return mixed
     */
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

        if (empty($taskId)) {
            throw new \RuntimeException(sprintf('Returned task ID is empty, suggesting the task was not created properly: %s', var_export($taskData, true)));
        }

        $this->update($taskId, $taskData);

        if (!empty($taskData['subtasks'])) {
            foreach($taskData['subtasks'] as $subtask) {
                $this->createSubtask($taskId, $subtask);
            }
        }

        if (isset($taskData['responsibles'])) {
            foreach ($taskData['responsibles'] as $userId) {
                $this->addResponsible($taskId, $userId);
            }
        }

        return $taskId;
    }

    /**
     * @param $taskId
     * @param $subtaskData
     *
     * @return array|mixed
     * @todo Move subtasks into separate class
     */
    public function createSubtask($taskId, $subtaskData)
    {
        if (empty($taskId)) {
            throw new InvalidArgumentException('Task ID should not be empty');
        }

        if (empty($subtaskData)) {
            return false;
        }

        if (empty($subtaskData['title'])) {
            return false;
        }

        return $this->client->post('tasks/' . $taskId . '/subtasks', ['subtask' => $subtaskData]);
    }

    /**
     * @param $taskId
     * @param $taskData
     *
     * @return array|mixed
     */
    public function update($taskId, $taskData)
    {
        if (empty($taskId)) {
            throw new InvalidArgumentException('Task ID should not be empty');
        }

        return $this->client->put('tasks/' . $taskId, ['task' => $taskData]);
    }

    /**
     * @param $taskId
     * @param $userId
     *
     * @return array|mixed
     */
    public function addResponsible($taskId, $userId)
    {
        if (is_array($userId) && isset($userId['id'])) {
            $userId = $userId['id'];
        }

        if (empty($taskId)) {
            throw new InvalidArgumentException('Task ID should not be empty');
        }

        if (empty($userId)) {
            throw new InvalidArgumentException('User ID should not be empty');
        }

        return $this->client->put('tasks/' . $taskId . '/responsibles/' . $userId);
    }

    /**
     * @param $taskId
     * @param $userId
     *
     * @return array|mixed
     */
    public function removeResponsible($taskId, $userId)
    {
        if (is_array($userId) && isset($userId['id'])) {
            $userId = $userId['id'];
        }

        if (empty($taskId)) {
            throw new InvalidArgumentException('Task ID should not be empty');
        }

        if (empty($userId)) {
            throw new InvalidArgumentException('User ID should not be empty');
        }

        return $this->client->delete('tasks/' . $taskId . '/responsibles/' . $userId);
    }
}