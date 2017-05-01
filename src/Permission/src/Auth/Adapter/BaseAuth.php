<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 01.03.17
 * Time: 17:09
 */

namespace rollun\permission\Auth\Adapter;

use rollun\datastore\DataStore\Interfaces\DataStoresInterface;
use rollun\dic\InsideConstruct;
use rollun\permission\Auth\Adapter\Factory\AuthAdapterAbstractFactory;
use rollun\permission\Auth\Adapter\Interfaces\IdentityAdapterInterface;
use rollun\permission\Auth\Adapter\Resolver\UserDSResolver;
use rollun\permission\Auth\Middleware\Factory\UserResolverFactory;
use rollun\permission\Auth\RuntimeException;
use Zend\Authentication\Adapter\Http;
use Zend\Authentication\Result;
use Zend\Psr7Bridge\Psr7Response;
use Zend\Psr7Bridge\Psr7ServerRequest;

class BaseAuth extends AbstractWebAdapter implements IdentityAdapterInterface
{

    /** @var  DataStoresInterface */
    protected $userDS;

    protected $http;

    protected $defaultConfig = [
        'accept_schemes' => 'basic',
        AuthAdapterAbstractFactory::KEY_AC_REALM => AuthAdapterAbstractFactory::DEFAULT_REALM,
        'nonce_timeout' => 3600
    ];

    //TODO: make more universal. With comparable Http.
    /**
     * BaseAuth constructor.
     * @param array $config
     * @param DataStoresInterface|null $userDS
     * @throws RuntimeException
     */
    public function __construct(array $config = [], DataStoresInterface $userDS = null)
    {
        InsideConstruct::setConstructParams(['userDS' => UserResolverFactory::DEFAULT_USER_DS]);
        if (!isset($this->userDS)) {
            throw new RuntimeException("UserDS not set.");
        }
        $this->http = new Http(array_merge($this->defaultConfig, $config));
        $this->http->setBasicResolver(new UserDSResolver($this->userDS));
        parent::__construct($config);
    }

    /**
     * @return Result
     */
    public function identify()
    {
        $httpRequest = Psr7ServerRequest::toZend($this->request);
        $httpResponse = Psr7Response::toZend($this->response);

        $this->http->setRequest($httpRequest);
        $this->http->setResponse($httpResponse);

        $result = $this->http->authenticate();
        return $result;
    }
}
