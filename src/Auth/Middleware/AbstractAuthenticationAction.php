<?php
/**
 * Created by PhpStorm.
 * User: victorsecuring
 * Date: 25.02.17
 * Time: 12:21 PM
 */

namespace rollun\permission\Auth\Middleware;

use rollun\permission\Auth\Adapter\AbstractWebAdapter;
use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\AuthenticationServiceInterface;
use Zend\Stratigility\MiddlewareInterface;

abstract class AbstractAuthenticationAction implements MiddlewareInterface
{
    const KEY_IDENTITY = 'identity';

    /** @var  AbstractWebAdapter */
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
}