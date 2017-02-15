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
use Zend\Authentication\AuthenticationServiceInterface;
use Zend\Stratigility\MiddlewareInterface;

class RoleResolver implements MiddlewareInterface
{
    const DEFAULT_ROLE = 'guest';

    const KEY_ROLE_ATTRIBUTE = 'roles';

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
        $user = $request->getAttribute(IdentifyAction::KEY_USER);
        $roles = isset($user[static::KEY_ROLE_ATTRIBUTE]) ? $user[static::KEY_ROLE_ATTRIBUTE] : static::DEFAULT_ROLE;
        $request = $request->withAttribute(static::KEY_ROLE_ATTRIBUTE, $roles);

        if (isset($out)) {
            return $out($request, $response);
        }

        return $response;
    }
}
