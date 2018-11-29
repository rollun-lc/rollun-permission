<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\test\unit\Permission\Acl;

use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use rollun\permission\Acl\ResourceProducer\RouteAttribute;
use rollun\permission\Acl\ResourceProducer\RouteReceiver\RouteNameReceiverInterface;
use Psr\Http\Message\ServerRequestInterface as Request;

class RouteAttributeTest extends TestCase
{
    protected function createObject(RouteNameReceiverInterface $routeNameReceiver, $attributeName)
    {
        return new RouteAttribute($routeNameReceiver, $attributeName);
    }

    public function testCanProduceSuccess()
    {
        $attributeName = 'attributeName';

        /** @var MockObject|Request $request */
        $request = $this->getMockBuilder(Request::class)->getMock();
        $request->expects($this->once())->method('getAttribute')->with($attributeName)->willReturn(1);

        /** @var MockObject|RouteNameReceiverInterface $routeNameReceiver */
        $routeNameReceiver = $this->getMockBuilder(RouteNameReceiverInterface::class)->getMock();

        $object = $this->createObject($routeNameReceiver, $attributeName);
        $this->assertTrue($object->canProduce($request));
    }

    public function testCanProduceFailed()
    {
        $attributeName = 'attributeName';

        /** @var MockObject|Request $request */
        $request = $this->getMockBuilder(Request::class)->getMock();
        $request->expects($this->once())->method('getAttribute')->with($attributeName)->willReturn(0);

        /** @var MockObject|RouteNameReceiverInterface $routeNameReceiver */
        $routeNameReceiver = $this->getMockBuilder(RouteNameReceiverInterface::class)->getMock();

        $object = $this->createObject($routeNameReceiver, $attributeName);
        $this->assertFalse($object->canProduce($request));
    }

    public function testProduceSuccess()
    {
        $attributeName = 'attributeName';

        /** @var MockObject|Request $request */
        $request = $this->getMockBuilder(Request::class)->getMock();
        $request->expects($this->once())->method('getAttribute')->with($attributeName)->willReturn('a');

        /** @var MockObject|RouteNameReceiverInterface $routeNameReceiver */
        $routeNameReceiver = $this->getMockBuilder(RouteNameReceiverInterface::class)->getMock();
        $routeNameReceiver->expects($this->once())->method('receiveRouteName')->with($request)->willReturn('b');

        $object = $this->createObject($routeNameReceiver, $attributeName);
        $this->assertEquals('b-a', $object->produce($request));
    }

    public function testProduceFailed()
    {
        $this->expectException(InvalidArgumentException::class);
        $attributeName = 'attributeName';

        /** @var MockObject|Request $request */
        $request = $this->getMockBuilder(Request::class)->getMock();
        $request->expects($this->once())->method('getAttribute')->with($attributeName)->willReturn(0);

        /** @var MockObject|RouteNameReceiverInterface $routeNameReceiver */
        $routeNameReceiver = $this->getMockBuilder(RouteNameReceiverInterface::class)->getMock();

        $object = $this->createObject($routeNameReceiver, $attributeName);
        $this->assertEquals('b-a', $object->produce($request));
    }
}
