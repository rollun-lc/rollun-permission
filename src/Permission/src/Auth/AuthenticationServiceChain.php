<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\permission\Auth;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Zend\Expressive\Authentication\AuthenticationInterface;
use Zend\Expressive\Authentication\UserInterface;

class AuthenticationServiceChain implements AuthenticationInterface
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
     * @var ResponseInterface
     */
    protected $unauthorizedResponse;

    public function __construct(array $authenticationServices, callable $responseFactory = null)
    {
        // Ensures type safety of the composed factory
        $this->responseFactory = function () use ($responseFactory) : ResponseInterface {
            return $responseFactory();
        };

        $this->authenticationServices = $authenticationServices;
        $this->unauthorizedResponse = null;
    }

    /**
     * Chain available authentication services and try authenticate using it
     * If no one authentication service can authenticate return null
     * Other way return user from first service that can authenticate
     *
     * @param ServerRequestInterface $request
     * @return UserInterface
     */
    public function authenticate(ServerRequestInterface $request): ?UserInterface
    {
        foreach ($this->authenticationServices as $authenticationService) {
            $user = $authenticationService->authenticate($request);

            if (!is_null($user)) {
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
        if (!is_null($this->unauthorizedResponse)) {
            return $this->unauthorizedResponse;
        }

        if (!is_null($this->responseFactory)) {
            return ($this->responseFactory)($request)->withStatus(401);
        }

        throw new RuntimeException("Can't create unauthorized response");
    }
}
