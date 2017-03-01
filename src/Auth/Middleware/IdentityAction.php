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
use Zend\Stratigility\MiddlewareInterface;

class IdentityAction implements MiddlewareInterface
{
    const KEY_IDENTITY = 'identity';

    const DEFAULT_IDENTITY = '0';

    /** @var  AbstractWebAdapter[] */
    protected $adapters;

    public function __construct(array $adapters)
    {
        $this->adapters = $adapters;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param null|callable $out
     * @return null|Response
     */
    public function __invoke(Request $request, Response $response, callable $out = null)
    {
        /** @var  $adapter AbstractWebAdapter|IdentityAdapterInterface */
        foreach ($this->adapters as $adapter){
            $adapter->setRequest($request);
            $adapter->setResponse($response);
            $result = $adapter->identify();
            if($result->isValid()) {
                $identity = $result->getIdentity();
                break;
            }
        }
        $identity = isset($identity) ? $identity : static::DEFAULT_IDENTITY;
        $request = $request->withAttribute(static::KEY_IDENTITY, $identity);
        if (isset($out)) {
            return $out($request, $response);
        }
        return $response;
    }
}
