<?php

namespace rollun\permission\UserProvider;

class UserProviderChain
{
    /**
     * @var GetUserInterface[]
     */
    private $userProviders;

    public function __construct(array $userProviders)
    {
        $this->userProviders = $userProviders;
    }

    public function getUser(string $credential): ?array
    {
        foreach ($this->userProviders as $userProvider) {
            $user = $userProvider->getUser($credential);

            if ($user !== null) {
                return $user;
            }
        }

        return null;
    }
}