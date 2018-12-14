<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\permission\Authentication;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Expressive\Authentication\AuthenticationInterface;
use Zend\Expressive\Authentication\UserInterface;

class AuthenticationChain implements AuthenticationInterface
{
    /**
     * @var AuthenticationInterface[]
     */
    protected $authenticationServices;

    /**
     * @var callable
     */
    protected $responseFactory;

    /**
     * AuthenticationChain constructor.
     * @param array $authenticationServices
     * @param callable $responseFactory
     */
    public function __construct(array $authenticationServices, callable $responseFactory)
    {
        // Ensures type safety of the composed factory
        $this->responseFactory = function (ServerRequestInterface $request) use ($responseFactory) : ResponseInterface {
            return $responseFactory($request);
        };

        $this->authenticationServices = $authenticationServices;
    }

    /**
     * Chain available authentication services and try authenticate using it
     * If no one authentication service can authenticate return null
     * Other way return user from first service that can authenticate
     * Last authentication service provide an unauthorized response if no one can authenticate
     *
     * @param ServerRequestInterface $request
     * @return UserInterface
     */
    public function authenticate(ServerRequestInterface $request): ?UserInterface
    {
        foreach ($this->authenticationServices as $authenticationService) {
            $user = $authenticationService->authenticate($request);

            if ($user !== null) {
                return $user;
            }
        }

        return null;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function unauthorizedResponse(ServerRequestInterface $request): ResponseInterface
    {
        return ($this->responseFactory)($request)->withStatus(401);
    }
}
