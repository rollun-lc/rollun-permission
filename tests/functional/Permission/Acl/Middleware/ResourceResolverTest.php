<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\test\functional\Permission\Acl\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use rollun\datastore\DataStore\Memory;
use rollun\permission\Acl\Middleware\ResourceResolver;
use rollun\permission\Acl\ResourceProducer\RouteAttribute;
use rollun\permission\Acl\ResourceProducer\RouteReceiver\ExpressiveRouteName;
use Zend\Diactoros\ServerRequest;
use Zend\Expressive\Router\RouteResult;

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
        return new class($this, $testAssertion) implements DelegateInterface
        {
            protected $testCase;

            protected $testAssertion;

            public function __construct(TestCase $testCase, callable $testAssertion)
            {
                $this->testCase = $testCase;
                $this->testAssertion = $testAssertion;
            }

            public function process(ServerRequestInterface $request)
            {
                call_user_func($this->testAssertion, $this->testCase, $request);
            }
        };
    }
}
