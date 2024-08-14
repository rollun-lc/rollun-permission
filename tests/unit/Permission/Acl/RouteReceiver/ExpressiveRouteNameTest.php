<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\test\unit\Permission\Acl\RouteReceiver;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface as Request;
use PHPUnit\Framework\MockObject\MockObject;
use rollun\permission\Authorization\ResourceProducer\RouteReceiver\ExpressiveRouteName;
use Mezzio\Router\RouteResult;

class ExpressiveRouteNameTest extends TestCase
{
    public function testReceiveRouteName()
    {
        $matchedRouteName = 'matchedRouteName';

        $routeResult = $this->getMockBuilder(RouteResult::class)->disableOriginalConstructor()->getMock();
        $routeResult->expects($this->once())->method('getMatchedRouteName')->willReturn($matchedRouteName);

        /** @var MockObject|Request $request */
        $request = $this->getMockBuilder(Request::class)->getMock();
        $request->expects($this->once())->method('getAttribute')->with(RouteResult::class)->willReturn($routeResult);

        $object = new ExpressiveRouteName();
        $this->assertEquals($matchedRouteName, $object->receiveRouteName($request));
    }
}
