<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 01.03.17
 * Time: 15:53
 */

namespace rollun\permission\Auth\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use rollun\dic\InsideConstruct;
use rollun\permission\Auth\Adapter\AbstractWebAdapter;
use rollun\permission\Auth\Adapter\Session as SessionAuthAdapter;
use rollun\permission\Auth\AlreadyLogginException;
use rollun\permission\Auth\CredentialInvalidException;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Session\Container;

class RegisterAction extends AbstractAuthentication
{

    /** @var  Container */
    protected $sessionContainer;

    /** @var  LoggerInterface */
    protected $logger;

    /**
     * RegisterAction constructor.
     * @param AbstractWebAdapter $adapter
     * @param Container|null $sessionContainer
     * @throws \ReflectionException
     */
    public function __construct(AbstractWebAdapter $adapter, Container $sessionContainer = null)
    {
        InsideConstruct::setConstructParams(
            [
                'sessionStorage' => SessionAuthAdapter::DEFAULT_SESSION_SERVICE_NAME,
                'logger' => LoggerInterface::class,
            ]);
        if (!isset($this->sessionContainer)) {
            $this->sessionContainer = new Container(SessionAuthAdapter::DEFAULT_SESSION_SERVICE_NAME);
        }
        parent::__construct($adapter);
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * to the next middleware component to create the response.
     *
     * @param Request $request
     * @param DelegateInterface $delegate
     *
     * @return Response
     */
    public function process(Request $request, DelegateInterface $delegate)
    {
        if (!$this->sessionContainer->offsetExists(SessionAuthAdapter::DEFAULT_SESSION_MEMBER)) {
            $this->adapter->setRequest($request);
            $response = new EmptyResponse();
            $this->adapter->setResponse($response);
            $result = $this->adapter->register();
            if ($result->isValid()) {
                $identity = $result->getIdentity();
                //Add for block double register.
                $this->sessionContainer->offsetSet(SessionAuthAdapter::DEFAULT_SESSION_MEMBER, $identity);
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
        $response = $delegate->process($request);
        return $response;
    }
}
