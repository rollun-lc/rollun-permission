<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace rollun\permission\Authentication;

class BearerTokenClaims
{
    private string $subject;
    private string $audience;
    private string $tokenId;
    /** @var string[] */
    private array $scopes;

    /**
     * @param string[] $scopes
     */
    public function __construct(string $subject, string $audience, string $tokenId, array $scopes = [])
    {
        $this->subject = $subject;
        $this->audience = $audience;
        $this->tokenId = $tokenId;
        $this->scopes = array_values($scopes);
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getAudience(): string
    {
        return $this->audience;
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

