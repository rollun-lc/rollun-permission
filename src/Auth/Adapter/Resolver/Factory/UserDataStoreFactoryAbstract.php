<?php

/**
 * Created by PhpStorm.
 * User: root
 * Date: 15.02.17
 * Time: 16:19
 */

namespace rollun\permission\Auth\Adapter\Resolver;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class UserDataStoreFactoryAbstract implements AbstractFactoryInterface
{
    const KEY_USER_DS_RESOLVER = 'userDataStoreResolver';

    const KEY_DS_SERVICE = 'dataStoreService';

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
        $resolverConfig = $config[static::KEY_USER_DS_RESOLVER][$requestedName];
        if($container->has($resolverConfig[static::KEY_DS_SERVICE]))
        {
            $dataStore = $container->get($resolverConfig[static::KEY_DS_SERVICE]);
            return new UserDataStore($dataStore);
        }
        throw new ServiceNotFoundException($resolverConfig[static::KEY_DS_SERVICE] . " not found.");
    }

    /**
     * Can the factory create an instance for the service?
     *
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @return bool
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        $config = $container->get('config');
        return isset($config[static::KEY_USER_DS_RESOLVER][$requestedName][static::KEY_DS_SERVICE]);
    }
}
