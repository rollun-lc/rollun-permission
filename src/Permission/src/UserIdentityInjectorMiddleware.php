<?php
/**
 * Created by PhpStorm.
 * User: USER_T
 * Date: 05.02.2019
 * Time: 16:56
 */

namespace rollun\permission;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Expressive\Authentication\UserInterface;

class UserIdentityInjectorMiddleware implements MiddlewareInterface
{
    /**
     * Process an incoming server request.
     *
     * Processes an incoming server request in order to produce a response.
     * If unable to produce the response itself, it may delegate to the provided
     * request handler to do so.
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        $session = $request->getAttribute('session');
        $user = $session->get(UserInterface::class);

        if (isset($user['roles'])) {
            $identity['roles'] = $user['roles'];
            $identity['details'] = $user['details'] ?? [];
            $response = $response->withHeader('X-Identity', json_encode($identity));
        }

        return $response;
    }
}
