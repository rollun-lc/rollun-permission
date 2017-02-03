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
use Zend\Stratigility\MiddlewareInterface;

class ResourceResolver implements MiddlewareInterface
{
    /** @var  DataStoreAbstract */
    protected $resourceDataStore;

    public function __construct(DataStoreAbstract $dataStore)
    {
        $this->resourceDataStore = $dataStore;
    }

    /**
     * Process an incoming request and/or response.
     *
     * Accepts a server-side request and a response instance, and does
     * something with them.
     *
     * If the response is not complete and/or further processing would not
     * interfere with the work done in the middleware, or if the middleware
     * wants to delegate to another process, it can use the `$out` callable
     * if present.
     *
     * If the middleware does not return a value, execution of the current
     * request is considered complete, and the response instance provided will
     * be considered the response to return.
     *
     * Alternately, the middleware may return a response instance.
     *
     * Often, middleware will `return $out();`, with the assumption that a
     * later middleware will return a response.
     *
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
        $request = $request->withAttribute('resource', $resource);

        if (isset($out)) {
            return $out($request, $response);
        }

        return $response;
    }
}
