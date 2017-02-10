<?php

namespace rollun\permission\Auth;

use rollun\permission\Auth\Adapter\OpenIDAdapter;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\AuthenticationServiceInterface;
use Zend\Session\AbstractManager;
use Zend\Session\Container;
use Zend\Session\SessionManager;

/**
 * Created by PhpStorm.
 * User: root
 * Date: 30.01.17
 * Time: 14:24
 */
class OpenIDAuthManager
{

    const KEY_ASSESS_TOKEN = 'assess_token';

    const KEY_STATE = 'state';

    /** @var  OpenIDAdapter */
    protected $openIDAdapter;

    /** @var  AuthenticationService */
    protected $authService;

    /** @var  SessionManager */
    protected $sessionManger;

    /** @var  Container */
    protected $sessionContainer;

    public function __construct(AuthenticationService $authenticationService, SessionManager $sessionManager)
    {
        $this->authService = $authenticationService;
        $this->openIDAdapter = $authenticationService->getAdapter();
        $this->sessionManger = $sessionManager;
        $this->sessionContainer = new Container('SessionContainer', $sessionManager);
    }

    public function login($code, $state)
    {
        if ($this->authService->hasIdentity()) {
            throw new \Exception("Already logged in.");
        }

        if (isset($this->sessionContainer->{static::KEY_STATE}) &&
            strcmp($this->sessionContainer->{static::KEY_STATE}, $state) === 0
        ) {
            $this->openIDAdapter->setCode($code);
            $result = $this->authService->authenticate();
        } else {
            throw new \InvalidArgumentException("Invalid state parameter!");
        }
        return $result;
    }

    public function logout()
    {
        if (!$this->authService->hasIdentity()) {
            throw new \Exception("You not logged in.");
        }
        $this->sessionManger->expireSessionCookie();
        $this->authService->clearIdentity();
    }
}
