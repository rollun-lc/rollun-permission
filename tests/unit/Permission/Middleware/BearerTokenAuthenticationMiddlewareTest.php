<?php

declare(strict_types=1);

namespace rollun\test\unit\Permission\Middleware;

use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\ServerRequest;
use Mezzio\Authentication\UserInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use rollun\permission\Authentication\BearerTokenAuthenticatedIdentity;
use rollun\permission\Authentication\BearerTokenAuthenticationException;
use rollun\permission\Authentication\BearerTokenAuthenticator;
use rollun\permission\Middleware\BearerTokenAuthenticationMiddleware;

class BearerTokenAuthenticationMiddlewareTest extends TestCase
{
    public function testProcessSetsIdentityForValidBearerJwt(): void
    {
        $authenticator = $this->createMock(BearerTokenAuthenticator::class);
        $authenticator->expects($this->once())
            ->method('authenticate')
            ->with('eyJ.valid.token')
            ->willReturn(new BearerTokenAuthenticatedIdentity(
                'user-123',
                ['manager', 'viewer'],
                'John Doe',
                'crm-client',
                'token-jti-1',
                ['crm.read']
            ));

        $request = (new ServerRequest())->withHeader('Authorization', 'Bearer eyJ.valid.token');
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with($this->callback(static function ($handledRequest) {
                $user = $handledRequest->getAttribute(UserInterface::class);

                if (!$user instanceof UserInterface) {
                    return false;
                }

                if ($user->getIdentity() !== 'user-123') {
                    return false;
                }

                if ($user->getRoles() !== ['manager', 'viewer']) {
                    return false;
                }

                if (($user->getDetails()['name'] ?? null) !== 'John Doe') {
                    return false;
                }

                if ($handledRequest->getAttribute(BearerTokenAuthenticationMiddleware::ATTRIBUTE_CLIENT) !== 'crm-client') {
                    return false;
                }

                if ($handledRequest->getAttribute(BearerTokenAuthenticationMiddleware::ATTRIBUTE_TOKEN_ID) !== 'token-jti-1') {
                    return false;
                }

                return $handledRequest->getAttribute(BearerTokenAuthenticationMiddleware::ATTRIBUTE_SCOPES) === ['crm.read'];
            }))
            ->willReturn(new JsonResponse(['ok' => true], 200));

        $middleware = new BearerTokenAuthenticationMiddleware($authenticator);
        $response = $middleware->process($request, $handler);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testProcessReturns401WhenTokenSignatureIsInvalid(): void
    {
        $response = $this->processWithAuthenticatorException('Access token signature or claims are invalid.');
        $this->assertUnauthorizedResponse($response, 'Access token signature or claims are invalid.');
    }

    public function testProcessReturns401WhenTokenIsExpired(): void
    {
        $response = $this->processWithAuthenticatorException('Access token has expired.');
        $this->assertUnauthorizedResponse($response, 'Access token has expired.');
    }

    public function testProcessReturns401WhenTokenIsRevoked(): void
    {
        $response = $this->processWithAuthenticatorException('Access token has been revoked.');
        $this->assertUnauthorizedResponse($response, 'Access token has been revoked.');
    }

    public function testProcessReturns401WhenClientIsInactive(): void
    {
        $response = $this->processWithAuthenticatorException('OAuth client is inactive or untrusted.');
        $this->assertUnauthorizedResponse($response, 'OAuth client is inactive or untrusted.');
    }

    public function testProcessPassesThroughWithoutAuthorizationHeader(): void
    {
        $authenticator = $this->createMock(BearerTokenAuthenticator::class);
        $authenticator->expects($this->never())->method('authenticate');

        $request = new ServerRequest();
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with($this->callback(static function ($handledRequest) {
                return $handledRequest->getAttribute(UserInterface::class) === null;
            }))
            ->willReturn(new JsonResponse(['ok' => true], 200));

        $middleware = new BearerTokenAuthenticationMiddleware($authenticator);
        $response = $middleware->process($request, $handler);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testProcessPassesThroughForNonBearerHeader(): void
    {
        $authenticator = $this->createMock(BearerTokenAuthenticator::class);
        $authenticator->expects($this->never())->method('authenticate');

        $request = (new ServerRequest())->withHeader('Authorization', 'Basic QWxhZGRpbjpPcGVuU2VzYW1l');
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method('handle')->willReturn(new JsonResponse(['ok' => true], 200));

        $middleware = new BearerTokenAuthenticationMiddleware($authenticator);
        $response = $middleware->process($request, $handler);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testProcessPassesThroughForNonJwtBearerToken(): void
    {
        $authenticator = $this->createMock(BearerTokenAuthenticator::class);
        $authenticator->expects($this->never())->method('authenticate');

        $request = (new ServerRequest())->withHeader('Authorization', 'Bearer string-token');
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method('handle')->willReturn(new JsonResponse(['ok' => true], 200));

        $middleware = new BearerTokenAuthenticationMiddleware($authenticator);
        $response = $middleware->process($request, $handler);

        $this->assertSame(200, $response->getStatusCode());
    }

    private function processWithAuthenticatorException(string $errorDescription): ResponseInterface
    {
        $authenticator = $this->createMock(BearerTokenAuthenticator::class);
        $authenticator->expects($this->once())
            ->method('authenticate')
            ->with('eyJ.invalid.token')
            ->willThrowException(new BearerTokenAuthenticationException($errorDescription));

        $request = (new ServerRequest())->withHeader('Authorization', 'Bearer eyJ.invalid.token');
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())->method('handle');

        $middleware = new BearerTokenAuthenticationMiddleware($authenticator);

        return $middleware->process($request, $handler);
    }

    private function assertUnauthorizedResponse(ResponseInterface $response, string $expectedDescription): void
    {
        $this->assertSame(401, $response->getStatusCode());
        $decoded = json_decode((string)$response->getBody(), true);
        $this->assertSame(BearerTokenAuthenticationException::ERROR_CODE, $decoded['error'] ?? null);
        $this->assertSame($expectedDescription, $decoded['error_description'] ?? null);
    }
}

