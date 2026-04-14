<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace rollun\permission\Authentication;

class BearerTokenAuthenticatedIdentity
{
    private string $userId;
    /** @var string[] */
    private array $roles;
    private string $name;
    private string $clientName;
    private string $tokenId;
    /** @var string[] */
    private array $scopes;

    /**
     * @param string[] $roles
     * @param string[] $scopes
     */
    public function __construct(
        string $userId,
        array $roles,
        string $name,
        string $clientName,
        string $tokenId,
        array $scopes = []
    ) {
        $this->userId = $userId;
        $this->roles = array_values($roles);
        $this->name = $name;
        $this->clientName = $clientName;
        $this->tokenId = $tokenId;
        $this->scopes = array_values($scopes);
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getClientName(): string
    {
        return $this->clientName;
    }

    public function getTokenId(): string
    {
        return $this->tokenId;
    }

    /**
     * @return string[]
     */
    public function getScopes(): array
    {
        return $this->scopes;
    }
}

