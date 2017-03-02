<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 01.03.17
 * Time: 18:40
 */

namespace rollun\permission\Auth\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use rollun\permission\Auth\Adapter\AbstractWebAdapter;
use rollun\permission\Auth\Adapter\Interfaces\AuthenticatePrepareAdapterInterface;
use rollun\permission\Auth\AlreadyLogginException;
use rollun\permission\Auth\CredentialInvalidException;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\AuthenticationServiceInterface;
use Zend\Authentication\Result;

class AuthenticationPrepareAction extends AbstractAuthentication
{
    /**
     * Authentication user
     * @param Request $request
     * @param Response $response
     * @param null|callable $out
     * @return null|Response
     * @throws AlreadyLogginException
     * @throws CredentialInvalidException
     */
    public function __invoke(Request $request, Response $response, callable $out = null)
    {
        $this->adapter->setRequest($request);
        $this->adapter->setResponse($response);

        $result = $this->adapter->prepare();

        if ($result->isValid()) {
            $request = $this->adapter->getRequest();
            $response = $this->adapter->getResponse();
        }

        if (isset($out)) {
            return $out($request, $response);
        }
        return $response;
    }
}
