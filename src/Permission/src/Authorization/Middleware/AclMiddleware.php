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
use Zend\Permissions\Acl\AclInterface;

class AclMiddleware implements MiddlewareInterface
{
    /**
     * @var AclInterface
     */
    protected $acl;

    /**
     * @var RequestHandlerInterface
     */
    protected $accessForbiddenHandler;

    public function __construct(AclInterface $acl, RequestHandlerInterface $accessForbiddenHandler)
    {
        $this->acl = $acl;
        $this->accessForbiddenHandler = $accessForbiddenHandler;
    }

    /**
     * @param Request $request
     * @param RequestHandlerInterface $handler
     * @return Response
     */
    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        $roles = $request->getAttribute(RoleResolver::KEY_ATTRIBUTE_ROLE);
        $resource = $request->getAttribute(ResourceResolver::KEY_ATTRIBUTE_RESOURCE);
        $privilege = $request->getAttribute(PrivilegeResolver::KEY_ATTRIBUTE_PRIVILEGE);
        $isAllowed = false;

        if ($this->acl->hasResource($resource)) {
            foreach ($roles as $role) {
                if ($this->acl->isAllowed($role, $resource, $privilege)) {
                    $isAllowed = true;
                    break;
                }
            }
        }

        if (!$isAllowed) {
            return $this->accessForbiddenHandler->handle($request);
        }

        $response = $handler->handle($request);

        return $response;
    }
}
