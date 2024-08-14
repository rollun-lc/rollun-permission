<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\test\functional\Permission\Authorization\Middleware;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use rollun\datastore\DataStore\Memory;
use rollun\permission\Authorization\Middleware\ResourceResolver;
use rollun\permission\Authorization\ResourceProducer\RouteAttribute;
use rollun\permission\Authorization\ResourceProducer\RouteReceiver\ExpressiveRouteName;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;
use Mezzio\Router\RouteResult;

class ResourceResolverTest extends TestCase
{
    public function testProcessSuccess()
    {
        $routeName = 'abc';
        $routeAttributeValue = 'df';
        $resource = $routeName . '-' . $routeAttributeValue;
        $routeAttributeKey = 'routeAttributeKey';

        $request = new ServerRequest();
        $request = $request->withAttribute($routeAttributeKey, $routeAttributeValue);

        $routeResult = $this->getMockBuilder(RouteResult::class)->disableOriginalConstructor()->getMock();
        $routeResult->expects($this->once())->method('getMatchedRouteName')->willReturn($routeName);
        $request = $request->withAttribute(RouteResult::class, $routeResult);

        $resourceDataStores = new Memory();
        $resourceDataStores->create(
            [
                'name' => $resource,
            ]
        );

        $resourceProducers = [
            new RouteAttribute(new ExpressiveRouteName(), $routeAttributeKey),
        ];

        $object = new ResourceResolver($resourceDataStores, $resourceProducers);
        $delegator = $this->getDelegator(
            function (TestCase $testCase, ServerRequestInterface $request) use ($resource) {
                $testCase->assertEquals($request->getAttribute(ResourceResolver::KEY_ATTRIBUTE_RESOURCE), $resource);
            }
        );

        $object->process($request, $delegator);
    }
    public function testProcessSuccessWithMultiplyProducers()
    {
        $routeName = 'abc';
        $routeAttributeValue = 'df';
        $resource = $routeName . '-' . $routeAttributeValue;
        $routeAttributeKey1 = 'routeAttributeKey1';
        $routeAttributeKey2 = 'routeAttributeKey2';

        $request = new ServerRequest();
        $request = $request->withAttribute($routeAttributeKey2, $routeAttributeValue);

        $routeResult = $this->getMockBuilder(RouteResult::class)->disableOriginalConstructor()->getMock();
        $routeResult->expects($this->once())->method('getMatchedRouteName')->willReturn($routeName);
        $request = $request->withAttribute(RouteResult::class, $routeResult);

        $resourceDataStores = new Memory();
        $resourceDataStores->create(
            [
                'name' => $resource,
            ]
        );

        $resourceProducers = [
            new RouteAttribute(new ExpressiveRouteName(), $routeAttributeKey1),
            new RouteAttribute(new ExpressiveRouteName(), $routeAttributeKey2),
        ];

        $object = new ResourceResolver($resourceDataStores, $resourceProducers);
        $delegator = $this->getDelegator(
            function (TestCase $testCase, ServerRequestInterface $request) use ($resource) {
                $testCase->assertEquals($request->getAttribute(ResourceResolver::KEY_ATTRIBUTE_RESOURCE), $resource);
            }
        );

        $object->process($request, $delegator);
    }

    public function getDelegator(callable $testAssertion)
    {
        return new class($this, $testAssertion) implements RequestHandlerInterface
        {
            protected $testCase;

            protected $testAssertion;

            public function __construct(TestCase $testCase, callable $testAssertion)
            {
                $this->testCase = $testCase;
                $this->testAssertion = $testAssertion;
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                call_user_func($this->testAssertion, $this->testCase, $request);
                return new Response();
            }
        };
    }
}
