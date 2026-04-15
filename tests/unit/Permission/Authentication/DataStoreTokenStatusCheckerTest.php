<?php

declare(strict_types=1);

namespace rollun\test\unit\Permission\Authentication;

use PHPUnit\Framework\TestCase;
use rollun\datastore\DataStore\Interfaces\DataStoresInterface;
use rollun\permission\Authentication\BearerTokenClaims;
use rollun\permission\Authentication\DataStoreTokenStatusChecker;
use rollun\permission\Authentication\InactiveClientException;
use rollun\permission\Authentication\RevokedTokenException;
use rollun\permission\Authentication\TokenStatusCheckException;
use rollun\permission\DataStore\OAuthAccessTokensTable;
use rollun\permission\DataStore\OAuthClientsTable;
use RuntimeException;
use Xiag\Rql\Parser\Node\LimitNode;
use Xiag\Rql\Parser\Node\Query\ScalarOperator\EqNode;
use Xiag\Rql\Parser\Query;

class DataStoreTokenStatusCheckerTest extends TestCase
{
    public function testCheckAccessTokenSuccess(): void
    {
        $accessTokens = $this->createMock(DataStoresInterface::class);
        $accessTokens->expects($this->once())
            ->method('read')
            ->with('jti-1')
            ->willReturn($this->createValidTokenRow());

        $clients = $this->createMock(DataStoresInterface::class);
        $clients->expects($this->once())
            ->method('query')
            ->with($this->callback(function ($query) {
                if (!$query instanceof Query) {
                    return false;
                }

                $queryNode = $query->getQuery();
                if (!$queryNode instanceof EqNode) {
                    return false;
                }

                if ($queryNode->getField() !== OAuthClientsTable::FILED_NAME) {
                    return false;
                }

                if ($queryNode->getValue() !== 'crm-client') {
                    return false;
                }

                $limitNode = $query->getLimit();
                if (!$limitNode instanceof LimitNode) {
                    return false;
                }

                return $limitNode->getLimit() === 1;
            }))
            ->willReturn([$this->createActiveTrustedClientRow()]);

        $checker = new DataStoreTokenStatusChecker($accessTokens, $clients);
        $checker->checkAccessToken($this->createClaims());

        $this->addToAssertionCount(1);
    }

    public function testCheckAccessTokenThrowsWhenTokenNotFound(): void
    {
        $accessTokens = $this->createMock(DataStoresInterface::class);
        $accessTokens->expects($this->once())
            ->method('read')
            ->with('jti-1')
            ->willReturn(null);

        $clients = $this->createMock(DataStoresInterface::class);
        $clients->expects($this->never())->method('query');

        $checker = new DataStoreTokenStatusChecker($accessTokens, $clients);

        $this->expectException(RevokedTokenException::class);
        $this->expectExceptionMessage('Access token has been revoked.');

        $checker->checkAccessToken($this->createClaims());
    }

    public function testCheckAccessTokenThrowsWhenTokenRevoked(): void
    {
        $accessTokens = $this->createMock(DataStoresInterface::class);
        $accessTokens->expects($this->once())
            ->method('read')
            ->with('jti-1')
            ->willReturn($this->createValidTokenRow([OAuthAccessTokensTable::FILED_REVOKED => 1]));

        $clients = $this->createMock(DataStoresInterface::class);
        $clients->expects($this->never())->method('query');

        $checker = new DataStoreTokenStatusChecker($accessTokens, $clients);

        $this->expectException(RevokedTokenException::class);
        $this->expectExceptionMessage('Access token has been revoked.');

        $checker->checkAccessToken($this->createClaims());
    }

    public function testCheckAccessTokenThrowsWhenTokenExpired(): void
    {
        $accessTokens = $this->createMock(DataStoresInterface::class);
        $accessTokens->expects($this->once())
            ->method('read')
            ->with('jti-1')
            ->willReturn($this->createValidTokenRow([OAuthAccessTokensTable::FILED_EXPIRES_AT => '-1 hour']));

        $clients = $this->createMock(DataStoresInterface::class);
        $clients->expects($this->never())->method('query');

        $checker = new DataStoreTokenStatusChecker($accessTokens, $clients);

        $this->expectException(RevokedTokenException::class);
        $this->expectExceptionMessage('Access token has been revoked.');

        $checker->checkAccessToken($this->createClaims());
    }

    /**
     * @dataProvider invalidExpiresAtProvider
     */
    public function testCheckAccessTokenThrowsWhenTokenExpiresAtInvalid($expiresAt): void
    {
        $accessTokens = $this->createMock(DataStoresInterface::class);
        $accessTokens->expects($this->once())
            ->method('read')
            ->with('jti-1')
            ->willReturn($this->createValidTokenRow([OAuthAccessTokensTable::FILED_EXPIRES_AT => $expiresAt]));

        $clients = $this->createMock(DataStoresInterface::class);
        $clients->expects($this->never())->method('query');

        $checker = new DataStoreTokenStatusChecker($accessTokens, $clients);

        $this->expectException(RevokedTokenException::class);
        $this->expectExceptionMessage('Access token has been revoked.');

        $checker->checkAccessToken($this->createClaims());
    }

    public function invalidExpiresAtProvider(): array
    {
        return [
            'null' => [null],
            'empty string' => [''],
            'invalid string' => ['not-a-date'],
        ];
    }

    public function testCheckAccessTokenThrowsWhenClientNotFound(): void
    {
        $accessTokens = $this->createMock(DataStoresInterface::class);
        $accessTokens->method('read')->willReturn($this->createValidTokenRow());

        $clients = $this->createMock(DataStoresInterface::class);
        $clients->expects($this->once())->method('query')->willReturn([]);

        $checker = new DataStoreTokenStatusChecker($accessTokens, $clients);

        $this->expectException(InactiveClientException::class);
        $this->expectExceptionMessage('OAuth client is inactive or untrusted.');

        $checker->checkAccessToken($this->createClaims());
    }

    public function testCheckAccessTokenThrowsWhenClientRevoked(): void
    {
        $accessTokens = $this->createMock(DataStoresInterface::class);
        $accessTokens->method('read')->willReturn($this->createValidTokenRow());

        $clients = $this->createMock(DataStoresInterface::class);
        $clients->expects($this->once())
            ->method('query')
            ->willReturn([
                $this->createActiveTrustedClientRow([OAuthClientsTable::FILED_REVOKED => 1]),
            ]);

        $checker = new DataStoreTokenStatusChecker($accessTokens, $clients);

        $this->expectException(InactiveClientException::class);
        $this->expectExceptionMessage('OAuth client is inactive or untrusted.');

        $checker->checkAccessToken($this->createClaims());
    }

    public function testCheckAccessTokenThrowsWhenClientStatusIsNotActive(): void
    {
        $accessTokens = $this->createMock(DataStoresInterface::class);
        $accessTokens->method('read')->willReturn($this->createValidTokenRow());

        $clients = $this->createMock(DataStoresInterface::class);
        $clients->expects($this->once())
            ->method('query')
            ->willReturn([
                $this->createActiveTrustedClientRow([OAuthClientsTable::FILED_STATUS => 'disabled']),
            ]);

        $checker = new DataStoreTokenStatusChecker($accessTokens, $clients);

        $this->expectException(InactiveClientException::class);
        $this->expectExceptionMessage('OAuth client is inactive or untrusted.');

        $checker->checkAccessToken($this->createClaims());
    }

    public function testCheckAccessTokenThrowsWhenClientIsNotTrusted(): void
    {
        $accessTokens = $this->createMock(DataStoresInterface::class);
        $accessTokens->method('read')->willReturn($this->createValidTokenRow());

        $clients = $this->createMock(DataStoresInterface::class);
        $clients->expects($this->once())
            ->method('query')
            ->willReturn([
                $this->createActiveTrustedClientRow([OAuthClientsTable::FILED_IS_TRUSTED => 0]),
            ]);

        $checker = new DataStoreTokenStatusChecker($accessTokens, $clients);

        $this->expectException(InactiveClientException::class);
        $this->expectExceptionMessage('OAuth client is inactive or untrusted.');

        $checker->checkAccessToken($this->createClaims());
    }

    /**
     * @dataProvider revokedLikeValueProvider
     */
    public function testCheckAccessTokenFailsClosedForRevokedTokenField($revokedValue): void
    {
        $accessTokens = $this->createMock(DataStoresInterface::class);
        $accessTokens->expects($this->once())
            ->method('read')
            ->with('jti-1')
            ->willReturn($this->createValidTokenRow([OAuthAccessTokensTable::FILED_REVOKED => $revokedValue]));

        $clients = $this->createMock(DataStoresInterface::class);
        $clients->expects($this->never())->method('query');

        $checker = new DataStoreTokenStatusChecker($accessTokens, $clients);

        $this->expectException(RevokedTokenException::class);
        $this->expectExceptionMessage('Access token has been revoked.');

        $checker->checkAccessToken($this->createClaims());
    }

    /**
     * @dataProvider revokedLikeValueProvider
     */
    public function testCheckAccessTokenFailsClosedForRevokedClientField($revokedValue): void
    {
        $accessTokens = $this->createMock(DataStoresInterface::class);
        $accessTokens->method('read')->willReturn($this->createValidTokenRow());

        $clients = $this->createMock(DataStoresInterface::class);
        $clients->expects($this->once())
            ->method('query')
            ->willReturn([
                $this->createActiveTrustedClientRow([OAuthClientsTable::FILED_REVOKED => $revokedValue]),
            ]);

        $checker = new DataStoreTokenStatusChecker($accessTokens, $clients);

        $this->expectException(InactiveClientException::class);
        $this->expectExceptionMessage('OAuth client is inactive or untrusted.');

        $checker->checkAccessToken($this->createClaims());
    }

    public function revokedLikeValueProvider(): array
    {
        return [
            'null'              => [null],
            'empty string'      => [''],
            'unrecognized str'  => ['yes'],
            'numeric string 2'  => ['2'],
            'int 2'             => [2],
            'bool true'         => [true],
        ];
    }

    public function testCheckAccessTokenPassesForExplicitFalseRevokedValues(): void
    {
        $falsyValues = [0, '0', 'false', 'FALSE', false];

        foreach ($falsyValues as $value) {
            $accessTokens = $this->createMock(DataStoresInterface::class);
            $accessTokens->method('read')->willReturn(
                $this->createValidTokenRow([OAuthAccessTokensTable::FILED_REVOKED => $value])
            );

            $clients = $this->createMock(DataStoresInterface::class);
            $clients->method('query')->willReturn([$this->createActiveTrustedClientRow()]);

            $checker = new DataStoreTokenStatusChecker($accessTokens, $clients);
            $checker->checkAccessToken($this->createClaims());
        }

        $this->addToAssertionCount(1);
    }

    public function testCheckAccessTokenWrapsDataStoreException(): void
    {
        $original = new RuntimeException('datastore read failed');

        $accessTokens = $this->createMock(DataStoresInterface::class);
        $accessTokens->expects($this->once())
            ->method('read')
            ->with('jti-1')
            ->willThrowException($original);

        $clients = $this->createMock(DataStoresInterface::class);
        $clients->expects($this->never())->method('query');

        $checker = new DataStoreTokenStatusChecker($accessTokens, $clients);

        try {
            $checker->checkAccessToken($this->createClaims());
            $this->fail('Expected TokenStatusCheckException to be thrown.');
        } catch (TokenStatusCheckException $e) {
            $this->assertSame($original, $e->getPrevious());
        }
    }

    private function createClaims(): BearerTokenClaims
    {
        return new BearerTokenClaims('user-1', 'crm-client', 'jti-1');
    }

    private function createValidTokenRow(array $overrides = []): array
    {
        return array_merge([
            OAuthAccessTokensTable::FILED_ID => 'jti-1',
            OAuthAccessTokensTable::FILED_REVOKED => 0,
            OAuthAccessTokensTable::FILED_EXPIRES_AT => '+1 hour',
        ], $overrides);
    }

    private function createActiveTrustedClientRow(array $overrides = []): array
    {
        return array_merge([
            OAuthClientsTable::FILED_NAME => 'crm-client',
            OAuthClientsTable::FILED_REVOKED => 0,
            OAuthClientsTable::FILED_STATUS => 'active',
            OAuthClientsTable::FILED_IS_TRUSTED => 1,
        ], $overrides);
    }
}
