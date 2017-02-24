<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 15.02.17
 * Time: 16:33
 */

namespace rollun\permission\Auth\Adapter;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use rollun\api\Api\Google\Client\Web;
use rollun\dic\InsideConstruct;
use rollun\permission\Auth\Adapter\Resolver\OpenIDResolver;
use RuntimeException;
use Zend\Authentication\Adapter\Http\ResolverInterface;
use Zend\Authentication\Result;

class OpenID extends AbstractWebAdapter implements LogOutInterface
{

    /** @var  Web */
    protected $webClient;

    /**
     * OpenIDAdapter constructor.
     * @param array $config
     * @param Web $webClient
     */
    public function __construct(array $config, Web $webClient = null)
    {
        parent::__construct($config);
        InsideConstruct::setConstructParams(['webClient' => Web::class]);
        if (!isset($this->webClient)) {
            throw new RuntimeException("webClient not set");
        }
        if(isset($this->resolver)) {
            $this->resolver = new OpenIDResolver($this->webClient);
        }
    }

    /**
     * Performs an authentication attempt
     *
     * @return array|false|string|\Zend\Authentication\Result
     * @throws \Zend\Authentication\Adapter\Exception\ExceptionInterface If authentication cannot be performed
     */
    public function authenticate()
    {
        if (empty($this->request) || empty($this->response) || empty($this->webClient)) {
            throw new RuntimeException(
                'Request and Response and WebClient objects must be set before calling authenticate()'
            );
        }
        $query = $this->request->getQueryParams();
        //todo: rewrite to constant
        $code = isset($query['code']) ? $query['code'] : null;
        $state = isset($query[Web::KEY_STATE]) ? $query[Web::KEY_STATE] : "";

        if (!isset($code)) {
            return $this->challengeClient();
        }
        $result = $this->resolver->resolve($state, $this->realm, $code);
        return $result;
    }

    public function challengeClient()
    {
        $state = isset($query[Web::KEY_STATE]) ? $query[Web::KEY_STATE] : sha1(openssl_random_pseudo_bytes(1024));
        $response = $this->webClient->getAuthCodeRedirect($state);
        foreach ($this->response->getHeaders() as $headerName => $headerValue) {
            $response = $response->withHeader($headerName, $headerValue);
        }
        $this->request->withAttribute(Response::class, $response);
        return new Result(
            Result::FAILURE_CREDENTIAL_INVALID,
            null,
            ['Invalid or absent credentials; challenging client']
        );
    }

    /**
     * @return Web
     */
    public function getWebClient()
    {
        return $this->webClient;
    }

    /**
     * @param Web $webClient
     */
    public function setWebClient($webClient)
    {
        $this->webClient = $webClient;
    }

    /**
     * Erase user data.
     * @return void
     */
    public function logout()
    {
        $this->webClient->revokeToken();
    }
}
