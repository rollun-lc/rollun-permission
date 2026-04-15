<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace rollun\permission\Authentication;

use DateTimeImmutable;
use DateTimeInterface;
use rollun\datastore\DataStore\Interfaces\DataStoresInterface;
use rollun\permission\DataStore\OAuthAccessTokensTable;
use rollun\permission\DataStore\OAuthClientsTable;
use Throwable;
use Xiag\Rql\Parser\Node\LimitNode;
use Xiag\Rql\Parser\Node\Query\ScalarOperator\EqNode;
use Xiag\Rql\Parser\Query;

class DataStoreTokenStatusChecker implements TokenStatusCheckerInterface
{
    private DataStoresInterface $accessTokens;
    private DataStoresInterface $clients;

    public function __construct(DataStoresInterface $accessTokens, DataStoresInterface $clients)
    {
        $this->accessTokens = $accessTokens;
        $this->clients = $clients;
    }

    /**
     * @throws TokenStatusCheckException
     */
    public function checkAccessToken(BearerTokenClaims $claims): void
    {
        try {
            $tokenRow = $this->accessTokens->read($claims->getTokenId());
            if (!$this->isNonEmptyRow($tokenRow)) {
                throw new RevokedTokenException('Access token has been revoked.');
            }

            if ($this->isTruthy($tokenRow[OAuthAccessTokensTable::FILED_REVOKED] ?? null)) {
                throw new RevokedTokenException('Access token has been revoked.');
            }

            if ($this->isExpired($tokenRow[OAuthAccessTokensTable::FILED_EXPIRES_AT] ?? null)) {
                throw new RevokedTokenException('Access token has been revoked.');
            }

            $clientRow = $this->findClientByName($claims->getAudience());
            if (!$this->isNonEmptyRow($clientRow)) {
                throw new InactiveClientException('OAuth client is inactive or untrusted.');
            }

            if ($this->isTruthy($clientRow[OAuthClientsTable::FILED_REVOKED] ?? null)) {
                throw new InactiveClientException('OAuth client is inactive or untrusted.');
            }

            if ((string)($clientRow[OAuthClientsTable::FILED_STATUS] ?? '') !== 'active') {
                throw new InactiveClientException('OAuth client is inactive or untrusted.');
            }

            if (!$this->isTruthy($clientRow[OAuthClientsTable::FILED_IS_TRUSTED] ?? null)) {
                throw new InactiveClientException('OAuth client is inactive or untrusted.');
            }
        } catch (TokenStatusCheckException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new TokenStatusCheckException('Unable to check access token status.', 0, $e);
        }
    }

    private function findClientByName(string $audience): ?array
    {
        $query = new Query();
        $query->setQuery(new EqNode(OAuthClientsTable::FILED_NAME, $audience));
        $query->setLimit(new LimitNode(1));

        $rows = $this->clients->query($query);
        $row = array_shift($rows);

        return is_array($row) ? $row : null;
    }

    private function isNonEmptyRow($row): bool
    {
        return is_array($row) && $row !== [];
    }

    private function isTruthy($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return (int)$value === 1;
        }

        if (is_string($value)) {
            if (is_numeric($value)) {
                return (int)$value === 1;
            }

            return strtolower($value) === 'true';
        }

        return false;
    }

    private function isExpired($expiresAt): bool
    {
        if ($expiresAt instanceof DateTimeInterface) {
            $expiresAtDateTime = DateTimeImmutable::createFromInterface($expiresAt);
        } elseif (is_string($expiresAt)) {
            if (trim($expiresAt) === '') {
                return true;
            }

            try {
                $expiresAtDateTime = new DateTimeImmutable($expiresAt);
            } catch (Throwable $e) {
                return true;
            }
        } else {
            return true;
        }

        return $expiresAtDateTime < new DateTimeImmutable();
    }
}
