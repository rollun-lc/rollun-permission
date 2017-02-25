<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 10.02.17
 * Time: 13:06
 */

namespace rollun\permission\Auth\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use rollun\permission\Auth\AlreadyLogginException;
use rollun\permission\Auth\CredentialInvalidException;

class QuickAuthenticationAction extends AbstractAuthenticationAction
{
    /**
     * Quick authentication user
     * @param Request $request
     * @param Response $response
     * @param null|callable $out
     * @return null|Response
     * @throws AlreadyLogginException
     * @throws CredentialInvalidException
     */
    public function __invoke(Request $request, Response $response, callable $out = null)
    {
        if (!$this->authenticationService->hasIdentity()) {
            $this->adapter->setRequest($request);
            $this->adapter->setResponse($response);
            $result = $this->authenticationService->authenticate($this->adapter);
            if ($result->isValid()) {
                $identity = $result->getIdentity();
                $request = $request->withAttribute(static::KEY_IDENTITY, $identity);
            }
        }
        if (isset($out)) {
            return $out($request, $response);
        }
        return $response;
    }
}
