<?php

namespace rollun\test\unit\Permission\Authorization\Middleware;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Laminas\Permissions\Acl\AclInterface;
use Psr\Log\LoggerInterface;
use rollun\permission\Authorization\Middleware\AclMiddleware;
use rollun\permission\Authorization\Middleware\RoleResolver;
use rollun\permission\Authorization\Middleware\ResourceResolver;
use rollun\permission\Authorization\Middleware\PrivilegeResolver;

class AclMiddlewareTest extends TestCase
{
    public function testLogsErrorAndForbidsWhenApiDatastore()
    {
        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn('/foo/bar');

        $request = $this->createMock(Request::class);
        $request->method('getUri')->willReturn($uri);
        $request->method('getAttribute')->willReturnMap([
            [RoleResolver::KEY_ATTRIBUTE_ROLE, null, ['guest']],
            [ResourceResolver::KEY_ATTRIBUTE_RESOURCE, null, 'api-datastore'],
            [PrivilegeResolver::KEY_ATTRIBUTE_PRIVILEGE, null, 'read'],
        ]);

        $acl = $this->createMock(AclInterface::class);
        $acl->expects($this->once())
            ->method('hasResource')
            ->with('api-datastore')
            ->willReturn(true);
        $acl->expects($this->once())
            ->method('isAllowed')
            ->with('guest', 'api-datastore', 'read')
            ->willReturn(false);

        $forbiddenResponse = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
        $forbiddenHandler = $this->createMock(Handler::class);
        $forbiddenHandler->expects($this->once())
            ->method('handle')
            ->with($request)
            ->willReturn($forbiddenResponse);

        $middleware = new AclMiddleware($acl, $forbiddenHandler);
        $response = $middleware->process($request, $this->createMock(Handler::class));

        $this->assertSame($forbiddenResponse, $response);
    }

    public function testDoesNotLogErrorWhenOtherResource()
    {
        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn('/baz');

        $request = $this->createMock(Request::class);
        $request->method('getUri')->willReturn($uri);
        $request->method('getAttribute')->willReturnMap([
            [RoleResolver::KEY_ATTRIBUTE_ROLE, null, ['user']],
            [ResourceResolver::KEY_ATTRIBUTE_RESOURCE, null, 'other-resource'],
            [PrivilegeResolver::KEY_ATTRIBUTE_PRIVILEGE, null, 'write'],
        ]);

        $acl = $this->createMock(AclInterface::class);
        $acl->method('hasResource')->with('other-resource')->willReturn(true);
        $acl->method('isAllowed')->with('user', 'other-resource', 'write')->willReturn(true);

        $okResponse = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
        $mainHandler = $this->createMock(Handler::class);
        $mainHandler->expects($this->once())
            ->method('handle')
            ->with($request)
            ->willReturn($okResponse);

        $forbiddenHandler = $this->createMock(Handler::class);

        $middleware = new AclMiddleware($acl, $forbiddenHandler);
        $response = $middleware->process($request, $mainHandler);

        $this->assertSame($okResponse, $response);
    }
}