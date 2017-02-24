<?php
/**
 * Created by PhpStorm.
 * User: victorsecuring
 * Date: 24.02.17
 * Time: 4:43 PM
 */

namespace rollun\permission\Auth\Adapter;


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use rollun\dic\InsideConstruct;
use rollun\permission\Auth\Adapter\Resolver\UserDSResolver;
use Zend\Authentication\Adapter\Http;
use Zend\Authentication\Adapter\Http\ResolverInterface;
use Zend\Psr7Bridge\Psr7Response;
use Zend\Psr7Bridge\Psr7ServerRequest;
use Zend\Http\Response as HTTPResponse;
use Zend\Http\Request as HTTPRequest;

class BaseAuth extends AbstractWebAdapter
{
    /** @var  Http */
    protected $http;

    public function __construct(array $config, Http $http = null)
    {
        parent::__construct($config);
        InsideConstruct::setConstructParams(['http' => Http::class]);
        if(!isset($this->http)) {
            $this->http = new Http($config);
        }
        if(isset($this->resolver)) {
            $this->resolver = new UserDSResolver();
        }
    }

    /**
     * Performs an authentication attempt
     * @return \Zend\Authentication\Result
     * @throws \Zend\Authentication\Adapter\Exception\ExceptionInterface If authentication cannot be performed
     */
    public function authenticate()
    {
        $httpRequest = Psr7ServerRequest::toZend($this->request);
        $httpResponse = Psr7Response::toZend($this->response);

        $this->http->setRequest($httpRequest);
        $this->http->setResponse($httpResponse);
        $this->http->setBasicResolver($this->resolver);

        $result = $this->http->authenticate();

        $response = Psr7Response::fromZend($httpResponse);
        $this->request = $this->request->withAttribute('responseData', ['data' => $httpResponse->getBody()])
            ->withAttribute(Response::class, $response);
        return $result;
    }
}