<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\permission\Acl\ResourceProducer;

use Interop\Container\ContainerInterface;
use InvalidArgumentException;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;

/**
 * Create instance of RouteAttribute using 'config' service as array in ContainerInterface
 *
 * Config example:
 * <code>
 *  [
 *      RouteAttributeAbstractFactory::class => [
 *          'requestedName' => [
 *              'class' => RouteAttribute::class, // optional
 *              'routeNameReceiver' => 'routeNameReceiverService',
 *              'attributeName' => 'resourceName',
 *          ]
 *      ]
 *  ]
 * </code>
 *
 * Class RouteAttributeAbstractFactory
 * @package rollun\permission\Acl\ResourceProducer
 */
class RouteAttributeAbstractFactory implements AbstractFactoryInterface
{
    const KEY = self::class;

    const DEFAULT_CLASS = RouteAttribute::class;

    const KEY_ROUTE_NAME_RECEIVER = 'routeNameReceiver';

    const KEY_ATTRIBUTE_NAME = 'attributeName';

    const KEY_CLASS = 'class';

    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @return bool
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        return isset($container->get('config')[self::class][$requestedName]);
    }

    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return RouteAttribute
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $serviceConfig = $container->get('config')[self::class][$requestedName];

        if (!isset($serviceConfig[self::KEY_ATTRIBUTE_NAME])) {
            throw new InvalidArgumentException("Missing '" . self::KEY_ATTRIBUTE_NAME . "' option");
        }

        if (!isset($serviceConfig[self::KEY_ROUTE_NAME_RECEIVER])) {
            throw new InvalidArgumentException("Missing '" . self::KEY_ROUTE_NAME_RECEIVER . "' option");
        }

        $class = $serviceConfig[self::KEY_CLASS] ?? self::DEFAULT_CLASS;
        $attributeName = $serviceConfig[self::KEY_ATTRIBUTE_NAME];
        $routeNameReceiver = $container->get($serviceConfig[self::KEY_ROUTE_NAME_RECEIVER]);

        return new $class($routeNameReceiver, $attributeName);
    }
}
