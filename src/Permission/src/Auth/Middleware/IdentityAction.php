<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 01.03.17
 * Time: 15:07
 */

namespace rollun\permission\Auth\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use rollun\permission\Auth\Adapter\AbstractWebAdapter;
use rollun\permission\Auth\Adapter\Interfaces\AuthenticateAdapterInterface;
use rollun\permission\Auth\Adapter\Interfaces\AuthenticatePrepareAdapterInterface;
use rollun\permission\Auth\Adapter\Interfaces\IdentityAdapterInterface;
use Zend\Diactoros\Response\EmptyResponse;

class IdentityAction implements MiddlewareInterface
{
    const KEY_ATTRIBUTE_IDENTITY = 'identity';

    const DEFAULT_IDENTITY = '0';

    /** @var  AbstractWebAdapter[] */
    protected $adapters;

    public function __construct(array $adapters)
    {
        $this->adapters = $adapters;
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
        /** @var  $adapter AbstractWebAdapter|IdentityAdapterInterface */
        foreach ($this->adapters as $adapter){
            $adapter->setRequest($request);
            $emptyResponse = new EmptyResponse();
            $adapter->setResponse($emptyResponse);
            $result = $adapter->identify();
            if($result->isValid()) {
                $identity = $result->getIdentity();
                break;
            }
        }
        $identity = isset($identity) ? $identity : static::DEFAULT_IDENTITY;
        $request = $request->withAttribute(static::KEY_ATTRIBUTE_IDENTITY, $identity);
        $response = $delegate->process($request);
        return $response;
    }
}
