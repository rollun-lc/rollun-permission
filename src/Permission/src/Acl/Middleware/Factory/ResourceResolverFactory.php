<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 02.02.17
 * Time: 17:19
 */

namespace rollun\permission\Acl\Middleware\Factory;

use Interop\Container\ContainerInterface;
use rollun\permission\Acl\Factory\AclFromDataStoreFactory;
use rollun\permission\Acl\Middleware\ResourceResolver;
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
class ResourceResolverFactory implements AbstractFactoryInterface
{
    const KEY = self::class;

    const KEY_RESOURCE_PRODUCERS = 'resourceProducers';

    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return object|ResourceResolver
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');
        $resourceServiceName = $config[AclFromDataStoreFactory::KEY_ACL][AclFromDataStoreFactory::KEY_DS_RESOURCE_SERVICE]
            ?? null;

        if (empty($resourceServiceName)) {
            throw new ServiceNotCreatedException('ACL config missing');
        }

        $dataStorePrivilege = $container->get($resourceServiceName);
        $serviceConfig = $config[self::class][$requestedName];

        if (!isset($serviceConfig[self::KEY_RESOURCE_PRODUCERS]) && !is_array($serviceConfig[self::KEY_RESOURCE_PRODUCERS])) {
            throw new ServiceNotCreatedException("Invalid option '" . self::KEY_RESOURCE_PRODUCERS . "'");
        }

        $resourceProducers = [];

        foreach ($serviceConfig[self::KEY_RESOURCE_PRODUCERS] as $resourceProducerServiceName) {
            $resourceProducers[] = $container->get($resourceProducerServiceName);
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
