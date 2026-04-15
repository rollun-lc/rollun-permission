<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace rollun\permission\Middleware;

use Laminas\Diactoros\Response\JsonResponse;
use Mezzio\Authentication\DefaultUser;
use Mezzio\Authentication\UserInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use rollun\permission\Authentication\BearerTokenAuthenticationException;
use rollun\permission\Authentication\BearerTokenAuthenticator;

class BearerTokenAuthenticationMiddleware implements MiddlewareInterface
{
    public const ATTRIBUTE_CLIENT = 'oauth2_client';
    public const ATTRIBUTE_TOKEN_ID = 'oauth2_token_id';
    public const ATTRIBUTE_SCOPES = 'oauth2_scopes';

    private BearerTokenAuthenticator $authenticator;

    public function __construct(BearerTokenAuthenticator $authenticator)
    {
        $this->authenticator = $authenticator;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $authorizationHeaders = $request->getHeader('Authorization');
        if (count($authorizationHeaders) !== 1) {
            return $handler->handle($request);
        }

        $authorizationHeader = trim((string)$authorizationHeaders[0]);
        if (!preg_match('/^Bearer\s+(?P<token>\S+)$/i', $authorizationHeader, $matches)) {
            return $handler->handle($request);
        }

        $token = (string)$matches['token'];

        // Bearer tokens that are not JWT belong to another auth flow and should pass through.
        if (strpos($token, 'eyJ') !== 0) {
            return $handler->handle($request);
        }

        try {
            $identity = $this->authenticator->authenticate($token);
        } catch (BearerTokenAuthenticationException $exception) {
            $message = $exception->getMessage();
            $safeDescription = $this->sanitizeHeaderValue($message);
            $headerValue = sprintf(
                'Bearer error="%s", error_description="%s"',
                BearerTokenAuthenticationException::ERROR_CODE,
                $safeDescription
            );

            return new JsonResponse(
                [
                    'error' => BearerTokenAuthenticationException::ERROR_CODE,
                    'error_description' => $message,
                ],
                401,
                ['WWW-Authenticate' => $headerValue]
            );
        }

        $user = new DefaultUser(
            $identity->getUserId(),
            $identity->getRoles(),
            ['name' => $identity->getName()]
        );

        $request = $request
            ->withAttribute(UserInterface::class, $user)
            ->withAttribute(self::ATTRIBUTE_CLIENT, $identity->getClientName())
            ->withAttribute(self::ATTRIBUTE_TOKEN_ID, $identity->getTokenId())
            ->withAttribute(self::ATTRIBUTE_SCOPES, $identity->getScopes());

        return $handler->handle($request);
    }

    private function sanitizeHeaderValue(string $value): string
    {
        return (string)preg_replace('/[^\x20-\x21\x23-\x7E]/', ' ', $value);
    }
}
