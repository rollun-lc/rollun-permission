<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 02.02.17
 * Time: 17:05
 */

namespace rollun\permission\Acl\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UriInterface;
use rollun\datastore\DataStore\DataStoreAbstract;
use rollun\datastore\DataStore\Interfaces\DataStoresInterface;
use Zend\Stratigility\MiddlewareInterface;

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
     *
     * {@inheritdoc}
     *
     * Add resource to request attribute.
     * @param Request $request
     * @param Response $response
     * @param null|callable $out
     * @return null|Response
     */
    public function __invoke(Request $request, Response $response, callable $out = null)
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

        if (isset($out)) {
            return $out($request, $response);
        }

        return $response;
    }
}
