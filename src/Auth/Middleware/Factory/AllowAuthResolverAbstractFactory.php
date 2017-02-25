<?php
/**
 * Created by PhpStorm.
 * User: victorsecuring
 * Date: 25.02.17
 * Time: 10:27 AM
 */

namespace rollun\permission\Auth\Middleware\Factory;


use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use rollun\permission\Auth\Middleware\AllowAuthResolver;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;

class AllowAuthResolverAbstractFactory implements AbstractFactoryInterface
{

    const KEY_ALLOW_AUTH_RESOLVER = 'allowAuthResolver';

    const KEY_ALLOW_AUTH = 'allowAuth';

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
        return isset($config[static::KEY_ALLOW_AUTH_RESOLVER][$requestedName]);
    }

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
        $factoryConfig =$config[static::KEY_ALLOW_AUTH_RESOLVER][$requestedName];

        if(!isset($factoryConfig[static::KEY_ALLOW_AUTH])) {
            throw new ServiceNotCreatedException(static::KEY_ALLOW_AUTH . " config not set.");
        }
        $allowAuth = $container->get($factoryConfig[static::KEY_ALLOW_AUTH]);
        $allowAuthResolver = new AllowAuthResolver($allowAuth);
        return $allowAuthResolver;
    }
}