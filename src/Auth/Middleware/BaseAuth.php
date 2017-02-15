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
use rollun\datastore\DataStore\DataStoreAbstract;
use rollun\datastore\Rql\RqlQuery;
use rollun\permission\Auth\CredentialInvalidException;
use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Adapter\Http;
use Zend\Authentication\AuthenticationServiceInterface;
use Zend\Authentication\Result;
use Zend\Psr7Bridge\Psr7Response;
use Zend\Psr7Bridge\Psr7ServerRequest;
use Zend\Stratigility\MiddlewareInterface;
use \rollun\permission\Auth\Manager\BaseAuth as BaseAuthManager;

class BaseAuth implements MiddlewareInterface
{
    const KEY_IDENTITY = 'identity';

    /** @var  Http */
    protected $httpAdapter;

    /**
     * BaseAuth constructor.
     * @param AdapterInterface $adapter
     */
    public function __construct(AdapterInterface $adapter)
    {
        $this->httpAdapter = $adapter;
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
        $this->httpAdapter->setRequest(Psr7ServerRequest::toZend($request));
        $this->httpAdapter->setResponse(Psr7Response::toZend($response));
        $result = $this->httpAdapter->authenticate();
        if ($result->isValid()) {
            $identity = $result->getIdentity();
            $request = $request->withAttribute(static::KEY_IDENTITY, $identity);
        } else {
            throw new CredentialInvalidException("Auth credential error.");
        }

        if (isset($out)) {
            return $out($request, $response);
        }
        return $response;
    }
}
