<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\permission\Authorization\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Authentication\UserInterface;
use Mezzio\Helper\UrlHelper;
use Mezzio\Session\SessionInterface;
use Mezzio\Session\SessionMiddleware;

class AccessForbiddenHandler implements RequestHandlerInterface
{
    private $urlHelper;

    public function __construct(UrlHelper $urlHelper)
    {
        $this->urlHelper = $urlHelper;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $roles = $request->getAttribute(RoleResolver::KEY_ATTRIBUTE_ROLE);
        $resource = $request->getAttribute(ResourceResolver::KEY_ATTRIBUTE_RESOURCE);
        $privilege = $request->getAttribute(PrivilegeResolver::KEY_ATTRIBUTE_PRIVILEGE);
        $user = $request->getAttribute(UserInterface::class);

        $body[] = "Access forbidden for '{$user->getIdentity()}'";

        if (!empty($roles)) {
            $body[] = "with roles = " . json_encode($roles);
        }

        if (!empty($resource)) {
            $body[] = "with resource = '$resource'";
        }

        if (!empty($privilege)) {
            $body[] = "with privilege = '$privilege'";
        }

        if (current($request->getHeader('Accept')) != 'application/json' && $user->getIdentity() == 'guest') {

            /** @var SessionInterface $session */
            $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
            $basePath = $request->getUri()->getPath();
            if ($session && strstr($basePath, "api/") === false) {
                $session->set('base_url', $basePath);
            }

            return new RedirectResponse($this->urlHelper->generate('login-action'), 301,
                [
                    'Cache-Control' => 'no-cache',
                    'X-Base-Path' => $basePath
                ]);
        }

        return new JsonResponse(implode(', ', $body), 403);
    }
}
