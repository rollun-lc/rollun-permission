<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\test\unit\Permission\Acl;

use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Interop\Container\ContainerInterface;
use rollun\permission\Authorization\ResourceProducer\AbstractResourceProducerAbstractFactory;
use rollun\permission\Authorization\ResourceProducer\RouteAttributeAbstractFactory;
use rollun\permission\Authorization\ResourceProducer\RouteReceiver\RouteNameReceiverInterface;

class RouteAttributeAbstractFactoryTest extends TestCase
{
    public function testCanCreate()
    {
        $requestedName = 'requestedName';
        $config = [
            AbstractResourceProducerAbstractFactory::class => [
                RouteAttributeAbstractFactory::class => [
                    $requestedName => [
                        'requestedName' => 'someValue',
                    ],
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
//        $container->setService('config', [
//            RouteAttributeAbstractFactory::class => [
//                $requestedName => [
//                    RouteAttributeAbstractFactory::KEY_ROUTE_NAME_RECEIVER => $routeNameReceiverServiceName,
//                    RouteAttributeAbstractFactory::KEY_ATTRIBUTE_NAME => $attributeName,
//                ],
//            ],
//        ]);
        $container->setService('config', [
            AbstractResourceProducerAbstractFactory::class => [
                RouteAttributeAbstractFactory::class => [
                    $requestedName => [
                        RouteAttributeAbstractFactory::KEY_ROUTE_NAME_RECEIVER => $routeNameReceiverServiceName,
                        RouteAttributeAbstractFactory::KEY_ATTRIBUTE_NAME => $attributeName,
                    ],
                ],
            ],
        ]);

        $object = new RouteAttributeAbstractFactory();
        $createdObject = $object->__invoke($container, $requestedName);
//        $this->assertAttributeEquals($attributeName, 'attributeName', $createdObject);
//        $this->assertAttributeEquals($routeNameReceiver, 'routeNameReceiver', $createdObject);

        $reflection = new \ReflectionObject($createdObject);

        $attributeNameProp = $reflection->getProperty('attributeName');
        $attributeNameProp->setAccessible(true);
        $this->assertSame($attributeName, $attributeNameProp->getValue($createdObject));

        $routeReceiverProp = $reflection->getProperty('routeNameReceiver');
        $routeReceiverProp->setAccessible(true);
        $this->assertSame($routeNameReceiver, $routeReceiverProp->getValue($createdObject));
    }
}
