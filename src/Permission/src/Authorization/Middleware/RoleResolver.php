<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\permission\Authorization\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Expressive\Authentication\UserInterface;

class RoleResolver implements MiddlewareInterface
{
    const DEFAULT_ROLE = 'guest';

    const KEY_ATTRIBUTE_ROLE = 'roles';

    /**
     * @param Request $request
     * @param RequestHandlerInterface $handler
     * @return Response
     */
    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        /** @var UserInterface $user */
        $user = $request->getAttribute(UserInterface::class);
        $roles = $user->getRoles() ?: [self::DEFAULT_ROLE];
        $request = $request->withAttribute(static::KEY_ATTRIBUTE_ROLE, $roles);

        $response = $handler->handle($request);

        return $response;
    }
}
