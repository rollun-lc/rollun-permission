<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\permission\Authorization\ResourceProducer;

use Interop\Container\ContainerInterface;
use InvalidArgumentException;

/**
 * Create instance of RouteAttribute using 'config' service as array in ContainerInterface
 *
 * Config example:
 * <code>
 *  [
 *      AbstractResourceProducerAbstractFactory::KEY => [
 *          RouteAttributeAbstractFactory::class => [
 *              'requestedName' => [
 *                  'class' => RouteAttribute::class, // optional
 *                  'routeNameReceiver' => 'routeNameReceiverService',
 *                  'attributeName' => 'resourceName',
 *              ]
 *          ]
 *      ]
 *  ]
 * </code>
 *
 * Class RouteAttributeAbstractFactory
 * @package rollun\permission\Acl\ResourceProducer
 */
class RouteAttributeAbstractFactory extends RouteNameAbstractFactory
{
    const DEFAULT_CLASS = RouteAttribute::class;

    const KEY_ATTRIBUTE_NAME = 'attributeName';

    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return RouteAttribute
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $serviceConfig = $this->getServiceConfig($container, $requestedName);

        if (!isset($serviceConfig[self::KEY_ATTRIBUTE_NAME])) {
            throw new InvalidArgumentException("Missing '" . self::KEY_ATTRIBUTE_NAME . "' option");
        }

        $class = $serviceConfig[self::KEY_CLASS] ?? self::DEFAULT_CLASS;
        $attributeName = $serviceConfig[self::KEY_ATTRIBUTE_NAME];
        $routeNameReceiver = $this->getRouteNameReceiver($container, $serviceConfig);

        return new $class($routeNameReceiver, $attributeName);
    }
}
