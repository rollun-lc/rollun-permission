<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\test\unit\Permission\Acl;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Interop\Container\ContainerInterface;
use rollun\permission\Acl\ResourceProducer\RouteAttributeAbstractFactory;
use rollun\permission\Acl\ResourceProducer\RouteReceiver\RouteNameReceiverInterface;
use Zend\ServiceManager\ServiceManager;

class RouteAttributeAbstractFactoryTest extends TestCase
{
    public function testCanCreate()
    {
        $requestedName = 'requestedName';
        $config = [
            RouteAttributeAbstractFactory::class => [
                $requestedName => [

                ],
            ],
        ];

        /** @var ContainerInterface|MockObject $container */
        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $container->expects($this->once())->method('get')->with('config')->willReturn($config);
        $object = new RouteAttributeAbstractFactory();
        $this->assertTrue($object->canCreate($container, $requestedName));
    }

    public function testInvokeSuccess()
    {
        $routeNameReceiver = $this->getMockBuilder(RouteNameReceiverInterface::class)->getMock();
        $routeNameReceiverServiceName = 'routeNameReceiverServiceName';
        $attributeName = 'a';
        $requestedName = 'requestedName';

        $container = new ServiceManager();
        $container->setService($routeNameReceiverServiceName, $routeNameReceiver);
        $container->setService('config', [
            RouteAttributeAbstractFactory::class => [
                $requestedName => [
                    RouteAttributeAbstractFactory::KEY_ROUTE_NAME_RECEIVER => $routeNameReceiverServiceName,
                    RouteAttributeAbstractFactory::KEY_ATTRIBUTE_NAME => $attributeName,
                ],
            ],
        ]);

        $object = new RouteAttributeAbstractFactory();
        $createdObject = $object->__invoke($container, $requestedName);
        $this->assertAttributeEquals($attributeName, 'attributeName', $createdObject);
        $this->assertAttributeEquals($routeNameReceiver, 'routeNameReceiver', $createdObject);
    }
}
