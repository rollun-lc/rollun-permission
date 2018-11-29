<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\permission\Acl\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use rollun\permission\Acl\AccessForbiddenException;
use Zend\Permissions\Acl\AclInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;

class AclMiddleware implements MiddlewareInterface
{
    /**
     * @var AclInterface
     */
    protected $acl;

    public function __construct(AclInterface $acl)
    {
        $this->acl = $acl;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * to the next middleware component to create the response.
     *
     * @param Request $request
     * @param DelegateInterface $delegate
     *
     * @return Response
     * @throws AccessForbiddenException
     */
    public function process(Request $request, DelegateInterface $delegate)
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
            throw new AccessForbiddenException(
                "Access forbidden for 'roles:[" . implode($roles)
                . "];resource: $resource;method: {$request->getMethod()}'"
            );
        }

        $response = $delegate->process($request);

        return $response;
    }
}
