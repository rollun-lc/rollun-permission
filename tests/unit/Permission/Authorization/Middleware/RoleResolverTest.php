<?php

declare(strict_types=1);

namespace rollun\test\unit\Permission\Authorization\Middleware;

use Mezzio\Authentication\UserInterface;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Uri;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use rollun\permission\Authorization\Middleware\RoleResolver;

class RoleResolverTest extends TestCase
{
    public function testProcessSetsRolesFromUser(): void
    {
        // Создаём мок пользователя, возвращающего список ролей.
        $user = $this->createMock(UserInterface::class);
        $user->method('getRoles')->willReturn(['admin', 'editor']);

        $request = (new ServerRequest())
            ->withMethod('GET')
            ->withUri(new Uri('/'))
            ->withAttribute(UserInterface::class, $user);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with($this->callback(function ($req) {
                $roles = $req->getAttribute(RoleResolver::KEY_ATTRIBUTE_ROLE);
                return $roles === ['admin', 'editor'];
            }))
            ->willReturn(new Response());

        $middleware = new RoleResolver();
        $response = $middleware->process($request, $handler);

        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testProcessSetsDefaultRoleWhenUserHasNoRoles(): void
    {
        $user = $this->createMock(UserInterface::class);
        $user->method('getRoles')->willReturn([]);

        $request = (new ServerRequest())
            ->withMethod('GET')
            ->withUri(new Uri('/'))
            ->withAttribute(UserInterface::class, $user);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with($this->callback(function ($req) {
                $roles = $req->getAttribute(RoleResolver::KEY_ATTRIBUTE_ROLE);
                return $roles === ['guest'];
            }))
            ->willReturn(new Response());

        $middleware = new RoleResolver();
        $response = $middleware->process($request, $handler);

        $this->assertInstanceOf(ResponseInterface::class, $response);
    }
}
