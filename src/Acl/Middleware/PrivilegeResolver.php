<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 03.02.17
 * Time: 13:06
 */

namespace rollun\permission\Acl\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Zend\Stratigility\MiddlewareInterface;

class PrivilegeResolver implements MiddlewareInterface
{

    const KEY_PRIVILEGE_ATTRIBUTE = 'privilege';
    /**
     * {@inheritdoc}
     *
     * add privilege to request attribute
     *
     * @param Request $request
     * @param Response $response
     * @param null|callable $out
     * @return null|Response
     */
    public function __invoke(Request $request, Response $response, callable $out = null)
    {
        $privilege = $request->getMethod();
        $request = $request->withAttribute(static::KEY_PRIVILEGE_ATTRIBUTE, $privilege);

        if ($out) {
            $out($request, $response);
        }

        return $response;
    }
}
