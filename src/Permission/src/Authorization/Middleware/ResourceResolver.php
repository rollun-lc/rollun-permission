<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\permission\Authorization\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use rollun\datastore\DataStore\Interfaces\DataStoresInterface;
use rollun\permission\Authorization\ResourceProducer\ResourceProducerInterface;
use Xiag\Rql\Parser\Query;

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
     * @param Request $request
     * @param RequestHandlerInterface $handler
     * @return Response
     */
    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        $resource = 'none';

        $resourceList = $this->resourceDataStore->query(new Query());
        foreach ($resourceList as $item) {
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
        $response = $handler->handle($request);

        return $response;
    }
}
