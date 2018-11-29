<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\permission\Acl\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use rollun\permission\Auth\Middleware\UserResolver;

class RoleResolver implements MiddlewareInterface
{
    const DEFAULT_ROLE = 'guest';

    const KEY_ATTRIBUTE_ROLE = 'roles';

    /**
     * Process an incoming server request and return a response, optionally delegating
     * to the next middleware component to create the response.
     *
     * @param Request $request
     * @param DelegateInterface $delegate
     *
     * @return Response
     */
    public function process(Request $request, DelegateInterface $delegate)
    {
        $user = $request->getAttribute(UserResolver::KEY_ATTRIBUTE_USER);
        $roles = isset($user[static::KEY_ATTRIBUTE_ROLE]) ? $user[static::KEY_ATTRIBUTE_ROLE] : [static::DEFAULT_ROLE];
        $request = $request->withAttribute(static::KEY_ATTRIBUTE_ROLE, $roles);

        $response = $delegate->process($request);

        return $response;
    }
}
