<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 01.03.17
 * Time: 15:07
 */

namespace rollun\permission\Auth\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use rollun\permission\Auth\Adapter\AbstractWebAdapter;
use rollun\permission\Auth\Adapter\Interfaces\AuthenticateAdapterInterface;
use rollun\permission\Auth\Adapter\Interfaces\AuthenticatePrepareAdapterInterface;
use rollun\permission\Auth\Adapter\Interfaces\IdentityAdapterInterface;
use rollun\permission\Auth\Adapter\Interfaces\RegisterAdapterInterface;
use Zend\Stratigility\MiddlewareInterface;

abstract class AbstractAuthentication implements MiddlewareInterface
{
    const KEY_IDENTITY = IdentityAction::KEY_ATTRIBUTE_IDENTITY;

    const DEFAULT_IDENTITY = IdentityAction::DEFAULT_IDENTITY;

    /** @var  AbstractWebAdapter|AuthenticatePrepareAdapterInterface|AuthenticateAdapterInterface|RegisterAdapterInterface */
    protected $adapter;

    public function __construct(AbstractWebAdapter $adapter)
    {
        $this->adapter = $adapter;
    }
}
