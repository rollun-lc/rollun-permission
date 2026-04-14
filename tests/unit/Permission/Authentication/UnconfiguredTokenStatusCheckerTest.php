<?php

declare(strict_types=1);

namespace rollun\test\unit\Permission\Authentication;

use PHPUnit\Framework\TestCase;
use rollun\permission\Authentication\BearerTokenClaims;
use rollun\permission\Authentication\TokenStatusCheckerNotConfiguredException;
use rollun\permission\Authentication\UnconfiguredTokenStatusChecker;

class UnconfiguredTokenStatusCheckerTest extends TestCase
{
    public function testCheckAccessTokenThrowsMeaningfulException(): void
    {
        $checker = new UnconfiguredTokenStatusChecker();

        $this->expectException(TokenStatusCheckerNotConfiguredException::class);
        $this->expectExceptionMessage('TokenStatusCheckerInterface must be configured in the application container.');

        $checker->checkAccessToken(new BearerTokenClaims('u-1', 'client', 'jti'));
    }
}

