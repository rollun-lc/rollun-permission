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
use rollun\permission\Auth\CredentialInvalidException;
use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Adapter\Http;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\AuthenticationServiceInterface;
use Zend\Authentication\Result;
use Zend\Psr7Bridge\Psr7Response;
use Zend\Psr7Bridge\Psr7ServerRequest;
use Zend\Stratigility\MiddlewareInterface;

class AuthenticationAction implements MiddlewareInterface
{
    const KEY_IDENTITY = 'identity';

    /** @var  Http */
    protected $adapter;

    /** @var  AuthenticationService */
    protected $authenticationService;

    /**
     * BaseAuth constructor.
     * @param AdapterInterface $adapter
     * @param AuthenticationServiceInterface $authenticationService
     */
    public function __construct(AdapterInterface $adapter, AuthenticationServiceInterface $authenticationService)
    {
        $this->adapter = $adapter;
        $this->authenticationService = $authenticationService;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param null|callable $out
     * @return null|Response
     * @throws CredentialInvalidException
     */
    public function __invoke(Request $request, Response $response, callable $out = null)
    {
        $zendRequest = Psr7ServerRequest::toZend($request);
        $zendResponse = Psr7Response::toZend($response);
        $this->adapter->setRequest($zendRequest);
        $this->adapter->setResponse($zendResponse);
        $result = $this->authenticationService->authenticate($this->adapter);
        if ($result->isValid()) {
            $identity = $result->getIdentity();
            $request = $request->withAttribute(static::KEY_IDENTITY, $identity);
            $request = $request->withAttribute('returnResult', 'false');
        } else if ($result->getCode() === Result::FAILURE_CREDENTIAL_INVALID) {
            $response = Psr7Response::fromZend($zendResponse);
            $request->withAttribute('responseData', ['data' => $zendResponse->getBody()]);
            $request = $request->withAttribute(Response::class, $response);
            $request = $request->withAttribute('returnResult', 'true');
        } else {
            throw new CredentialInvalidException("Auth credential error.");
        }

        if (isset($out)) {
            return $out($request, $response);
        }
        return $response;
    }
}
