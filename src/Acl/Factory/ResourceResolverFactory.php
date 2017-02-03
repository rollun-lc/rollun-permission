<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 02.02.17
 * Time: 17:19
 */

namespace rollun\permission\Acl\Factory;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use rollun\datastore\DataStore\DataStoreAbstract;
use rollun\permission\Acl\Middleware\ResourceResolver;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\FactoryInterface;

class ResourceResolverFactory implements FactoryInterface
{

    /**
     * Create an object
     *
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return object
     * @throws ServiceNotFoundException if unable to resolve the service.
     * @throws ServiceNotCreatedException if an exception is raised when
     *     creating a service.
     * @throws ContainerException if any other error occurs
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');
        if (!isset(
            $config[AclFromDataStoreFactory::KEY_ACL][AclFromDataStoreFactory::KEY_DS_RESOURCE_SERVICE])
        ) {
            throw new ServiceNotCreatedException('Not set acl config');
        }
        if (!$container->has(
            $config[AclFromDataStoreFactory::KEY_ACL][AclFromDataStoreFactory::KEY_DS_RESOURCE_SERVICE])
        ) {
            throw new ServiceNotCreatedException('Not found dataStore service');
        }
        /** @var DataStoreAbstract $dataStorePrivilege */
        $dataStorePrivilege = $container->get(
            $config[AclFromDataStoreFactory::KEY_ACL][AclFromDataStoreFactory::KEY_DS_RESOURCE_SERVICE]);
        return new ResourceResolver($dataStorePrivilege);
    }
}
