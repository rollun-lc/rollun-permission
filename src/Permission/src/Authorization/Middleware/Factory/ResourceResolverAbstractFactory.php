<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\permission\Authorization\Middleware\Factory;

use Interop\Container\ContainerInterface;
use rollun\permission\Authorization\Factory\AclFromDataStoreFactory;
use rollun\permission\Authorization\Middleware\ResourceResolver;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;

/**
 * Create instance of ResourceResolver using 'config' service as array in ContainerInterface
 *
 * Config example:
 * <code>
 *  [
 *      ResourceResolverFactory::class => [
 *          'requestedName' => [
 *              'resourceProducers' => [
 *                  'resourceProducerServiceName1',
 *                  'resourceProducerServiceName2',
 *                  // ...
 *              ]
 *          ]
 *      ]
 *  ]
 * </code>
 *
 * Class ResourceResolverFactory
 * @package rollun\permission\Acl\Middleware\Factory
 */
class ResourceResolverAbstractFactory implements AbstractFactoryInterface
{
    const KEY_RESOURCE_PRODUCERS = 'resourceProducers';

    const KEY_SERVICE_NAME = 'serviceName';

    const KEY_PRIORITY = 'priority';

    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return object|ResourceResolver
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');
        $resourceServiceName = $config[AclFromDataStoreFactory::class][AclFromDataStoreFactory::KEY_DATASTORE_RESOURCE_SERVICE]
            ?? null;

        if (empty($resourceServiceName)) {
            throw new ServiceNotCreatedException('ACL config missing');
        }

        $dataStorePrivilege = $container->get($resourceServiceName);
        $serviceConfig = $config[self::class][$requestedName];

        if (!isset($serviceConfig[self::KEY_RESOURCE_PRODUCERS])
            && !is_array(
                $serviceConfig[self::KEY_RESOURCE_PRODUCERS]
            )) {
            throw new ServiceNotCreatedException("Invalid option '" . self::KEY_RESOURCE_PRODUCERS . "'");
        }

        $resourceProducers = [];

        // sort resource producers by priority
        usort($serviceConfig[self::KEY_RESOURCE_PRODUCERS], function ($a, $b) {
            if (!isset($a[self::KEY_PRIORITY]) || !isset($b[self::KEY_PRIORITY])) {
                throw new \InvalidArgumentException("Option '" . self::KEY_PRIORITY . "' is required");
            }
            return $a[self::KEY_PRIORITY] - $b[self::KEY_PRIORITY];
        });

        foreach ($serviceConfig[self::KEY_RESOURCE_PRODUCERS] as $resourceProducer) {
            if (!isset($resourceProducer[self::KEY_SERVICE_NAME])) {
                throw new \InvalidArgumentException("Option '" . self::KEY_SERVICE_NAME . "' is required");
            }
            $resourceProducers[] = $container->get($resourceProducer[self::KEY_SERVICE_NAME]);
        }

        return new ResourceResolver($dataStorePrivilege, $resourceProducers);
    }

    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @return bool
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        return isset($container->get('config')[self::class][$requestedName]);
    }
}
