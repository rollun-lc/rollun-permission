<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\permission\Acl\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use rollun\datastore\DataStore\Interfaces\DataStoresInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use rollun\permission\Acl\ResourceProducer\ResourceProducerInterface;

class ResourceResolver implements MiddlewareInterface
{
    const KEY_ATTRIBUTE_RESOURCE = 'resource';

    /**
     * @var DataStoresInterface
     */
    protected $resourceDataStore;

    /**
     * @var ResourceProducerInterface[]
     */
    protected $resourceProducers;

    public function __construct(DataStoresInterface $resourceDataStore, array $resourceProducers)
    {
        $this->resourceDataStore = $resourceDataStore;
        $this->resourceProducers = $resourceProducers;
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

        foreach ($this->resourceDataStore as $item) {
            foreach ($this->resourceProducers as $resourceProducer) {
                if (!$resourceProducer->canProduce($request)) {
                    continue;
                }

                if ($resourceProducer->produce($request) === $item['name']) {
                    $resource = $item['name'];
                    break;
                }
            }
        }

        $request = $request->withAttribute(static::KEY_ATTRIBUTE_RESOURCE, $resource);
        $response = $delegate->process($request);

        return $response;
    }
}
