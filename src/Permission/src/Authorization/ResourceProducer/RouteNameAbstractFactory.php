<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\permission\Authorization\ResourceProducer;

use Interop\Container\ContainerInterface;
use InvalidArgumentException;

/**
 * Create instance of RouteName using 'config' service as array in ContainerInterface
 *
 * Config example:
 * <code>
 *  [
 *      AbstractResourceProducerAbstractFactory::KEY => [
 *          RouteNameAbstractFactory::class => [
 *              'requestedName' => [
 *                  'class' => RouteName::class, // optional
 *                  'routeNameReceiver' => 'routeNameReceiverService',
 *              ]
 *          ]
 *      ]
 *  ]
 * </code>
 *
 * Class RouteNameAbstractFactory
 * @package rollun\permission\Acl\ResourceProducer
 */
class RouteNameAbstractFactory extends AbstractResourceProducerAbstractFactory
{
    const DEFAULT_CLASS = RouteName::class;

    const KEY_ROUTE_NAME_RECEIVER = 'routeNameReceiver';

    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return RouteAttribute
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $serviceConfig = $this->getServiceConfig($container, $requestedName);
        $class = $serviceConfig[self::KEY_CLASS] ?? self::DEFAULT_CLASS;
        $routeNameReceiver = $this->getRouteNameReceiver($container, $serviceConfig);

        return new $class($routeNameReceiver);
    }

    protected function getRouteNameReceiver(ContainerInterface $container, $serviceConfig)
    {
        if (!isset($serviceConfig[self::KEY_ROUTE_NAME_RECEIVER])) {
            throw new InvalidArgumentException("Missing '" . self::KEY_ROUTE_NAME_RECEIVER . "' option");
        }

        return $container->get($serviceConfig[self::KEY_ROUTE_NAME_RECEIVER]);
    }
}
