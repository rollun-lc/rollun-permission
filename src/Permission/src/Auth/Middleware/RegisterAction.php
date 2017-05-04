<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 01.03.17
 * Time: 15:53
 */

namespace rollun\permission\Auth\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use rollun\dic\InsideConstruct;
use rollun\logger\Logger;
use rollun\permission\Auth\Adapter\AbstractWebAdapter;
use rollun\permission\Auth\Adapter\Session as SessionAuthAdapter;
use rollun\permission\Auth\AlreadyLogginException;
use rollun\permission\Auth\CredentialInvalidException;
use Zend\Authentication\Storage\Session as SessionStorage;


class RegisterAction extends AbstractAuthentication
{
    const DEFAULT_SESSION_NAMESPACE = SessionAuthAdapter::DEFAULT_SESSION_NAMESPACE;

    const DEFAULT_SESSION_MEMBER = SessionAuthAdapter::DEFAULT_SESSION_MEMBER;

    const DEFAULT_SESSION_STORAGE_SERVICE = SessionAuthAdapter::DEFAULT_SESSION_STORAGE_SERVICE;

    /** @var  SessionStorage */
    protected $sessionStorage;

    /** @var  Logger */
    protected $logger;

    public function __construct(AbstractWebAdapter $adapter, SessionStorage $sessionStorage = null)
    {
        InsideConstruct::setConstructParams(
            [
                'sessionStorage' => static::DEFAULT_SESSION_STORAGE_SERVICE,
                'logger' => Logger::DEFAULT_LOGGER_SERVICE
            ]);
        if (!isset($this->sessionStorage)) {
            $this->sessionStorage = new SessionStorage(
                static::DEFAULT_SESSION_NAMESPACE,
                static::DEFAULT_SESSION_MEMBER
            );
        }
        parent::__construct($adapter);
    }

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
        if ($this->sessionStorage->isEmpty()) {
            $this->adapter->setRequest($request);
            $this->adapter->setResponse($response);
            $result = $this->adapter->register();
            if ($result->isValid()) {
                $identity = $result->getIdentity();
                //Add for block double register.
                $this->sessionStorage->write($identity);
                $request = $request->withAttribute(static::KEY_IDENTITY, $identity)
                    ->withAttribute('responseData', ['status' => 'Register success. Wait for confirm you user.']);
                $this->logger->debug("credential valid. Register $identity user. [". microtime(true) ."]");
            } else {
                $this->logger->debug("Register error. [". microtime(true) ."]");
                $request = $request->withAttribute('responseData', ['status' => 'Register error.']);
                //throw new CredentialInvalidException("Auth credential error.");
            }
        }else {
            $request = $request->withAttribute('responseData', ['status' => 'You already login.']);
            $this->logger->debug("User already register. [". microtime(true) ."]");
        }
        if (isset($out)) {
            return $out($request, $response);
        }
        return $response;
    }
}
