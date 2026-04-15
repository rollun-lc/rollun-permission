<?php

declare(strict_types=1);

namespace rollun\test\unit\Permission\Authentication;

use Laminas\Diactoros\ServerRequest;
use Mezzio\Authentication\DefaultUser;
use Mezzio\Authentication\UserInterface;
use PHPUnit\Framework\TestCase;
use rollun\permission\Authentication\RequestAttributeAuthentication;

class RequestAttributeAuthenticationTest extends TestCase
{
    public function testAuthenticateReturnsUserFromRequestAttribute(): void
    {
        $authentication = new RequestAttributeAuthentication();
        $expectedUser = new DefaultUser('u-1', ['admin'], ['name' => 'John']);
        $request = (new ServerRequest())->withAttribute(UserInterface::class, $expectedUser);

        $actualUser = $authentication->authenticate($request);

        $this->assertSame($expectedUser, $actualUser);
    }

    public function testAuthenticateReturnsNullWhenAttributeIsMissing(): void
    {
        $authentication = new RequestAttributeAuthentication();
        $request = new ServerRequest();

        $actualUser = $authentication->authenticate($request);

        $this->assertNull($actualUser);
    }
}

