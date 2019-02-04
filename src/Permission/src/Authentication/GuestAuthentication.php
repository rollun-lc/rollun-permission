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

class GuestAuthentication implements AuthenticationInterface
{
    /**
     * @var \Closure
     */
    protected $responseFactory;

    /**
     * @var \Closure
     */
    protected $userFactory;

    /**
     * GuestAuthentication constructor.
     * @param callable $userFactory
     * @param callable $responseFactory
     */
    public function __construct(callable $userFactory, callable $responseFactory)
    {
        // Ensures type safety of the composed factory
        $this->responseFactory = function (ServerRequestInterface $request) use ($responseFactory) : ResponseInterface {
            return $responseFactory($request);
        };

        // Provide type safety for the composed user factory.
        $this->userFactory = function (
            string $identity,
            array $roles = [],
            array $details = []
        ) use ($userFactory) : UserInterface {
            return $userFactory($identity, $roles, $details);
        };
    }

    /**
     * @param ServerRequestInterface $request
     * @return UserInterface|null
     */
    public function authenticate(ServerRequestInterface $request): ?UserInterface
    {
        return ($this->userFactory)('guest', ['guest']);
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function unauthorizedResponse(ServerRequestInterface $request): ResponseInterface
    {
        return ($this->responseFactory)($request)->withStatus(500);
    }
}
