<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 02.02.17
 * Time: 17:05
 */

namespace rollun\permission\Acl\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UriInterface;
use rollun\datastore\DataStore\DataStoreAbstract;
use rollun\datastore\DataStore\Interfaces\DataStoresInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;

class ResourceResolver implements MiddlewareInterface
{

    const KEY_ATTRIBUTE_RESOURCE = 'resource';

    /** @var  DataStoreAbstract */
    protected $resourceDataStore;

    public function __construct(DataStoresInterface $resourceDataStore)
    {
        $this->resourceDataStore = $resourceDataStore;
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
        $resource = 'none';
        $urlWithPath = rtrim($request->getUri() . '?' . $request->getUri()->getQuery(), '?');
        foreach($this->resourceDataStore as $item) {
            if(preg_match($item['pattern'], $urlWithPath)) {
                $resource = $item['name'];
                break;
            }
        }
        $request = $request->withAttribute(static::KEY_ATTRIBUTE_RESOURCE, $resource);

        $response = $delegate->process($request);
        return $response;
    }
}
