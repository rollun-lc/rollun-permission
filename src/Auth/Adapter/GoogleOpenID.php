<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 01.03.17
 * Time: 17:07
 */

namespace rollun\permission\Auth\Adapter;

use rollun\api\Api\Google\Client\Web;
use rollun\datastore\DataStore\Interfaces\DataStoresInterface;
use rollun\dic\InsideConstruct;
use rollun\permission\Auth\Adapter\Interfaces\AuthenticateAdapterInterface;
use rollun\permission\Auth\Adapter\Interfaces\AuthenticatePrepareAdapterInterface;
use rollun\permission\Auth\Middleware\Factory\UserResolverFactory;
use rollun\permission\Auth\RuntimeException;
use Zend\Authentication\Result;
use Psr\Http\Message\ResponseInterface as Response;

class GoogleOpenID extends AbstractWebAdapter implements AuthenticateAdapterInterface, AuthenticatePrepareAdapterInterface
{
    const DEFAULT_WEB_SERVICE = Web::class;
    /** @var Web */
    protected $webClient;

    /** @var  DataStoresInterface */
    protected $userDS;

    public function __construct(array $config, Web $webClient = null, DataStoresInterface $userDS = null)
    {
        InsideConstruct::setConstructParams(
            ['webClient' => static::DEFAULT_WEB_SERVICE, 'userDS' => UserResolverFactory::DEFAULT_USER_DS]
        );
        if (!isset($this->webClient)) {
            throw new RuntimeException("WebClient not set");
        }
        if (!isset($this->userDS)) {
            throw new RuntimeException("userDS not set");
        }
        parent::__construct($config);
    }


    /**
     * Performs an authentication attempt
     * @return Result
     * @throws RuntimeException
     */
    public function authenticate()
    {
        if (empty($this->request) || empty($this->response)) {
            throw new RuntimeException(
                'Request and Response objects must be set before calling authenticate()'
            );
        }
        $query = $this->request->getQueryParams();
        $code = isset($query['code']) ? $query['code'] : null;
        $state = isset($query[Web::KEY_STATE]) ? $query[Web::KEY_STATE] : "";
        try {
            if (!isset($code)) {
                return new Result(
                    Result::FAILURE,
                    null,
                    ["code not set."]
                );
            }
            if ($this->webClient->getResponseState() !== $state) {
                return new Result(
                    Result::FAILURE,
                    null,
                    ["State not equalse."]
                );
            }
            if ($this->webClient->authByCode($code)) {
                $userId = $this->webClient->getUserId();
                $user = $this->userDS->read($userId);
                if (!empty($user)) {
                    //unset($user['pass'])
                    return new Result(
                        Result::SUCCESS,
                        $user[$this->userDS->getIdentifier()],
                        ['Success credential']
                    );
                }
            }
            return new Result(
                Result::FAILURE,
                null,
                ['Fail credential']
            );
        } catch (\Exception $e) {
            return new Result(
                Result::FAILURE,
                null,
                [$e->getMessage()]
            );
        }
    }

    /**
     * @return Result
     */
    public function prepare()
    {
        $state = isset($query[Web::KEY_STATE]) ? $query[Web::KEY_STATE] : sha1(openssl_random_pseudo_bytes(1024));

        $response = $this->webClient->getAuthCodeRedirect($state);
        foreach ($this->response->getHeaders() as $headerName => $headerValue) {
            $response = $response->withHeader($headerName, $headerValue);
        }
        $this->response = $response;
        $this->request = $this->request->withAttribute(Response::class, $response);

        return new Result(
            Result::SUCCESS,
            null,
            ['Invalid or absent credentials; challenging client']
        );
    }
}
