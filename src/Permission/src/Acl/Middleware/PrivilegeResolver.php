<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 03.02.17
 * Time: 13:06
 */

namespace rollun\permission\Acl\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Interop\Http\ServerMiddleware\MiddlewareInterface;

class PrivilegeResolver implements MiddlewareInterface
{

    const KEY_ATTRIBUTE_PRIVILEGE = 'privilege';

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
        $privilege = $request->getMethod();
        $request = $request->withAttribute(static::KEY_ATTRIBUTE_PRIVILEGE, $privilege);

        $response = $delegate->process($request);
        return $response;
    }
}
