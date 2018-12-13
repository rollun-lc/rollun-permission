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

class PrivilegeResolver implements MiddlewareInterface
{
    const KEY_ATTRIBUTE_PRIVILEGE = 'privilege';

    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        $privilege = $request->getMethod();
        $request = $request->withAttribute(static::KEY_ATTRIBUTE_PRIVILEGE, $privilege);

        $response = $handler->handle($request);

        return $response;
    }
}
