<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 27.01.17
 * Time: 14:54
 */

namespace rollun\permission\Acl\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use rollun\permission\Auth\Middleware\IdentifyAction;
use rollun\permission\Auth\Middleware\UserResolver;
use Zend\Authentication\AuthenticationServiceInterface;
use Zend\Stratigility\MiddlewareInterface;

class RoleResolver implements MiddlewareInterface
{
    const DEFAULT_ROLE = 'guest';

    const KEY_ATTRIBUTE_ROLE = 'roles';

    /**
     *
     * {@inheritdoc}
     * Identification user. Get role or use default and set to attribute
     * @param Request $request
     * @param Response $response
     * @param null|callable $out
     * @return null|Response
     */
    public function __invoke(Request $request, Response $response, callable $out = null)
    {
        $user = $request->getAttribute(UserResolver::KEY_ATTRIBUTE_USER);
        $roles = isset($user[static::KEY_ATTRIBUTE_ROLE]) ? $user[static::KEY_ATTRIBUTE_ROLE] : [static::DEFAULT_ROLE];
        $request = $request->withAttribute(static::KEY_ATTRIBUTE_ROLE, $roles);

        if (isset($out)) {
            return $out($request, $response);
        }

        return $response;
    }
}
