<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace rollun\permission\Authentication;

use DateTimeImmutable;
use PDO;
use PDOException;

class OAuth2ServerTokenStatusRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @throws BearerTokenAuthenticationException
     */
    public function assertAccessTokenIsActive(string $tokenId): void
    {
        try {
            $statement = $this->pdo->prepare(
                'SELECT revoked, expires_at FROM oauth2_server.oauth_access_tokens WHERE id = :jti LIMIT 1'
            );
            $statement->execute([':jti' => $tokenId]);
            $row = $statement->fetch();
        } catch (PDOException $e) {
            throw new BearerTokenAuthenticationException('Unable to validate access token state.', 0, $e);
        }

        if (!$row) {
            throw new BearerTokenAuthenticationException('Access token is unknown.');
        }

        $revoked = (int)($row['revoked'] ?? 0) === 1;
        $expired = $this->isExpired((string)($row['expires_at'] ?? ''));
        if ($revoked || $expired) {
            throw new BearerTokenAuthenticationException('Access token has been revoked.');
        }
    }

    /**
     * @throws BearerTokenAuthenticationException
     */
    public function assertClientIsActiveAndTrusted(string $clientName): void
    {
        try {
            $statement = $this->pdo->prepare(
                'SELECT revoked, status, is_trusted FROM oauth2_server.oauth_clients WHERE name = :aud LIMIT 1'
            );
            $statement->execute([':aud' => $clientName]);
            $row = $statement->fetch();
        } catch (PDOException $e) {
            throw new BearerTokenAuthenticationException('Unable to validate OAuth client state.', 0, $e);
        }

        if (!$row) {
            throw new BearerTokenAuthenticationException('OAuth client is inactive or untrusted.');
        }

        $isRevoked = (int)($row['revoked'] ?? 0) === 1;
        $isActive = (string)($row['status'] ?? '') === 'active';
        $isTrusted = (int)($row['is_trusted'] ?? 0) === 1;

        if ($isRevoked || !$isActive || !$isTrusted) {
            throw new BearerTokenAuthenticationException('OAuth client is inactive or untrusted.');
        }
    }

    private function isExpired(string $expiresAt): bool
    {
        if ($expiresAt === '') {
            return true;
        }

        try {
            $expirationDate = new DateTimeImmutable($expiresAt);
        } catch (\Exception $e) {
            return true;
        }

        return $expirationDate <= new DateTimeImmutable('now');
    }
}

