<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 27.01.17
 * Time: 14:56
 */

namespace rollun\permission\Acl\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use rollun\permission\Acl\AccessForbiddenException;
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\AclInterface;
use Zend\Stratigility\MiddlewareInterface;

class AclMiddleware implements MiddlewareInterface
{

    /** @var  AclInterface */
    protected $acl;

    public function __construct(AclInterface $acl)
    {
        $this->acl = $acl;
    }

    /**
     * Process an incoming request and/or response.
     *
     * Accepts a server-side request and a response instance, and does
     * something with them.
     *
     * If the response is not complete and/or further processing would not
     * interfere with the work done in the middleware, or if the middleware
     * wants to delegate to another process, it can use the `$out` callable
     * if present.
     *
     * If the middleware does not return a value, execution of the current
     * request is considered complete, and the response instance provided will
     * be considered the response to return.
     *
     * Alternately, the middleware may return a response instance.
     *
     * Often, middleware will `return $out();`, with the assumption that a
     * later middleware will return a response.
     *
     * @param Request $request
     * @param Response $response
     * @param null|callable $out
     * @return null|Response
     * @throws AccessForbiddenException
     */
    public function __invoke(Request $request, Response $response, callable $out = null)
    {
        $roles = $request->getAttribute(RoleResolver::KEY_ROLE_ATTRIBUTE);
        $resource = $request->getAttribute(ResourceResolver::KEY_RESOURCE_ATTRIBUTE);
        $privilege = $request->getAttribute(PrivilegeResolver::KEY_PRIVILEGE_ATTRIBUTE);
        $isAllowed = false;

        if($this->acl->hasResource($resource)) {
            foreach ($roles as $role) {
                if ($this->acl->isAllowed($role, $resource, $privilege)) {
                    $isAllowed = true;
                    break;
                }
            }
        }

        if ($isAllowed) {
            throw new AccessForbiddenException(
                "Access forbidden for 'roles:[" . implode($roles) . "];resource: $resource;method: {$request->getMethod()}'"
            );
        }

        if (isset($out)) {
            return $out($request, $response);
        }

        return $response;
    }
}
