<?php
namespace Yireo\ProducteevClient\Resource;

use \Yireo\ProducteevClient\Resource;

// @todo: Missing overview of users

class Users extends Resource
{
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

    public function findByEmail($email)
    {
        $data = $this->client->get('users/search', ['email' => $email]);

        if (empty($data['users'])) {
            throw new \Exception('No users found');
        }

        return $data['users'][0];
    }

    public function searchByEmail($email)
    {
        $data = $this->client->get('users/search', ['email' => $email]);

        return $data['users'];
    }
}