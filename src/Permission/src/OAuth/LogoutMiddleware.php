<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\permission\OAuth;

use Laminas\Diactoros\Response;
use Mezzio\Authentication\Session\Exception\MissingSessionContainerException;
use Mezzio\Authentication\UserInterface;
use Mezzio\Session\SessionInterface;
use Mezzio\Session\SessionMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class LogoutMiddleware implements MiddlewareInterface
{
    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);

        if (!$session instanceof SessionInterface) {
            throw MissingSessionContainerException::create();
        }

        if ($session->has(UserInterface::class)) {
            $session->unset(UserInterface::class);
        }

        return (new Response())->withStatus(200);
    }
}
