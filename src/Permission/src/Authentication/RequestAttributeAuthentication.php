<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace rollun\permission\Authentication;

use Laminas\Diactoros\Response;
use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Authentication\UserInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class RequestAttributeAuthentication implements AuthenticationInterface
{
    public function authenticate(ServerRequestInterface $request): ?UserInterface
    {
        $user = $request->getAttribute(UserInterface::class);

        return $user instanceof UserInterface ? $user : null;
    }

    public function unauthorizedResponse(ServerRequestInterface $request): ResponseInterface
    {
        return new Response('php://memory', 401);
    }
}

