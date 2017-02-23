<?php

/**
 * Created by PhpStorm.
 * User: root
 * Date: 15.02.17
 * Time: 16:19
 */

namespace rollun\permission\Auth\Adapter\Resolver\Factory;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use rollun\permission\Auth\Adapter\Resolver\UserDSResolver;
use rollun\permission\Auth\Middleware\Factory\UserResolverFactory;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class UserDSResolverAbstractFactory implements AbstractFactoryInterface
{
    const KEY_RESOLVER = 'userDataStoreResolver';

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
        $resolverConfig = $config[static::KEY_RESOLVER][$requestedName];
        $dsService = isset($resolverConfig[static::KEY_DS_SERVICE]) ?
            $resolverConfig[static::KEY_DS_SERVICE] : UserResolverFactory::DEFAULT_USER_DS;
        if($container->has($dsService))
        {
            $dataStore = $container->get($dsService);
            return new UserDSResolver($dataStore);
        }
        throw new ServiceNotFoundException("$dsService service not found.");
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
        return isset($config[static::KEY_RESOLVER][$requestedName][static::KEY_DS_SERVICE]);
    }
}
