<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 15.02.17
 * Time: 16:33
 */

namespace rollun\permission\Auth\Adapter;

use InvalidArgumentException;
use rollun\api\Api\Google\Client\Web;
use rollun\datastore\DataStore\DataStoreAbstract;
use RuntimeException;
use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Adapter\Http\ResolverInterface;
use Zend\Authentication\Adapter\ValidatableAdapterInterface;
use Zend\Authentication\Result;
use Zend\Http\Request as HTTPRequest;
use Zend\Http\Response as HTTPResponse;
use \rollun\permission\Auth\Adapter\Resolver\OpenIDResolver as OpenIDResolver;

class OpenID implements AdapterInterface, LogOutInterface
{

    /** @var  HTTPRequest */
    protected $request;

    /** @var  HTTPResponse */
    protected $response;

    /** @var  ResolverInterface */
    protected $resolver;

    /** @var string */
    protected $realm;

    /** @var  Web */
    protected $webClient;

    /**
     * OpenIDAdapter constructor.
     * @param array $config
     * @param Web $webClient
     */
    public function __construct(array $config)
    {
        // Double-quotes are used to delimit the realm string in the HTTP header,
        // and colons are field delimiters in the password file.
        if (empty($config['realm']) ||
            !ctype_print($config['realm']) ||
            strpos($config['realm'], ':') !== false ||
            strpos($config['realm'], '"') !== false
        ) {
            throw new InvalidArgumentException(
                'Config key \'realm\' is required, and must contain only printable characters,'
                . 'excluding quotation marks and colons'
            );
        } else {
            $this->realm = $config['realm'];
        }
    }

    /**
     * @param HTTPRequest $request
     */
    public function setRequest(HTTPRequest $request)
    {
        $this->request = $request;
    }

    /**
     * @param HTTPResponse $response
     */
    public function setResponse(HTTPResponse $response)
    {
        $this->response = $response;
    }

    /**
     * @param ResolverInterface $resolver
     */
    public function setResolver(ResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * Performs an authentication attempt
     *
     * @return array|false|string|\Zend\Authentication\Result
     * @throws \Zend\Authentication\Adapter\Exception\ExceptionInterface If authentication cannot be performed
     */
    public function authenticate()
    {
        if (empty($this->request) || empty($this->response) ||  empty($this->webClient)) {
            throw new RuntimeException(
                'Request and Response and WebClient objects must be set before calling authenticate()'
            );
        }
        $code = $this->request->getQuery('code', null);
        $state = $this->request->getQuery('state', "");
        if (!isset($code)) {
            return $this->challengeClient();
        }
        $result = $this->resolver->resolve($state, $this->realm, $code);
        return $result;
    }

    public function challengeClient()
    {
        $state = $this->request->getQuery('state', sha1(openssl_random_pseudo_bytes(1024)));
        $response = $this->webClient->getAuthCodeRedirect($state);

        $headers = $this->response->getHeaders();
        foreach ($response->getHeaders() as $name => $value) {
            $headers->addHeaderLine($name, $value);
        }
        $this->response->setStatusCode($response->getStatusCode());
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
     * @return HTTPResponse
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return HTTPRequest
     */
    public function getRequest()
    {
        return $this->request;
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
