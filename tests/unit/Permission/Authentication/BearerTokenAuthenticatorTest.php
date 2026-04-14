<?php

declare(strict_types=1);

namespace rollun\test\unit\Permission\Authentication;

use PHPUnit\Framework\TestCase;
use rollun\permission\Authentication\BearerTokenAuthenticator;
use rollun\permission\Authentication\BearerTokenClaims;
use rollun\permission\Authentication\JwtAccessTokenValidatorInterface;
use rollun\permission\Authentication\TokenStatusCheckerInterface;
use rollun\permission\Authentication\UserRolesResolver;
use rollun\permission\DataStore\AclUsersTable;
use rollun\permission\UserProvider\UserProviderChain;

class BearerTokenAuthenticatorTest extends TestCase
{
    public function testAuthenticateChecksTokenStatusAndBuildsIdentity(): void
    {
        $claims = new BearerTokenClaims(
            'u-1',
            'crm-client',
            'jti-1',
            ['crm.read']
        );

        $validator = $this->createMock(JwtAccessTokenValidatorInterface::class);
        $validator->expects($this->once())
            ->method('validate')
            ->with('jwt-token')
            ->willReturn($claims);

        $statusChecker = $this->createMock(TokenStatusCheckerInterface::class);
        $statusChecker->expects($this->once())
            ->method('checkAccessToken')
            ->with($claims);

        $userProvider = $this->createMock(UserProviderChain::class);
        $userProvider->expects($this->once())
            ->method('getUser')
            ->with('u-1')
            ->willReturn([
                AclUsersTable::FILED_ID => 'u-1',
                AclUsersTable::FILED_NAME => 'John Doe',
            ]);

        $rolesResolver = $this->createMock(UserRolesResolver::class);
        $rolesResolver->expects($this->once())
            ->method('getRolesByUserId')
            ->with('u-1')
            ->willReturn(['admin', 'manager']);

        $authenticator = new BearerTokenAuthenticator(
            $validator,
            $statusChecker,
            $userProvider,
            $rolesResolver
        );

        $identity = $authenticator->authenticate('jwt-token');

        $this->assertSame('u-1', $identity->getUserId());
        $this->assertSame('crm-client', $identity->getClientName());
        $this->assertSame('jti-1', $identity->getTokenId());
        $this->assertSame(['admin', 'manager'], $identity->getRoles());
        $this->assertSame(['crm.read'], $identity->getScopes());
        $this->assertSame('John Doe', $identity->getName());
    }
}

