<?php

namespace rollun\permission\UserProvider;

use rollun\datastore\DataStore\Interfaces\DataStoresInterface;

class GetUserById implements GetUserInterface
{
    private $users;

    public function __construct(
        DataStoresInterface $users
    ) {
        $this->users = $users;
    }

    public function getUser(string $credential): ?array
    {
        return $this->users->read($credential);
    }
}