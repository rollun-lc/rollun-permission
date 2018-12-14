<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\permission\OAuth;

use Exception;
use Traversable;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Expressive\Authentication\UserInterface;

class LoginMiddleware extends CredentialMiddleware
{
    const ACTION = 'login';

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws Exception
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->googleClient->setRedirectUri($this->actionToRedirectUri(self::ACTION));

        if ($this->getSession($request)->has(UserInterface::class)) {
            return $this->getAuthorizedResponse($request);
        }

        if (!$this->isOAuthAuthenticated($request)) {
            return $this->getUnAuthorizedResponse($request);
        }

        $credential = (string)$this->googleClient->getIdToken();
        $user = $this->userRepository->authenticate($credential);

        if ($user !== null) {
            $this->getSession($request)->set(
                UserInterface::class,
                [
                    'username' => $user->getIdentity(),
                    'roles' => iterator_to_array($this->getUserRoles($user)),
                    'details' => $user->getDetails(),
                ]
            );
            $this->getSession($request)->regenerate();

            return $this->getAuthorizedResponse($request);
        }

        return $this->getUnAuthorizedResponse($request);
    }

    /**
     * Convert the iterable user roles to a Traversable
     *
     * @param UserInterface $user
     * @return Traversable
     */
    private function getUserRoles(UserInterface $user): Traversable
    {
        return yield from $user->getRoles();
    }
}
