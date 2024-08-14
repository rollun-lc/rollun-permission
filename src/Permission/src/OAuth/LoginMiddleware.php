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
use Mezzio\Authentication\UserInterface;
use Mezzio\Session\SessionPersistenceInterface;

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
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    protected function getAuthorizedResponse(ServerRequestInterface $request): ResponseInterface
    {
        $response = parent::getAuthorizedResponse($request);
        $session = $this->getSession($request);
        $userIdentity = $session->get(UserInterface::class);
        $path = $session->get('base_url');
        $response = $response->withHeader('X-Redirect-Path', $path ?? '-');
        if ($path) {
            $response = $response->withHeader("Location", $path)
                ->withStatus(301);
        }

        $response = $response->withHeader('X-User-Identity', json_encode($userIdentity));

        return $response;
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
