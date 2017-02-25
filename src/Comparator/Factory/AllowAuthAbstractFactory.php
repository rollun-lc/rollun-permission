<?php
/**
 * Created by PhpStorm.
 * User: victorsecuring
 * Date: 25.02.17
 * Time: 10:40 AM
 */

namespace rollun\permission\Comparator\Factory;


use Interop\Container\ContainerInterface;
use rollun\permission\Comparator\AllowAuth;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;

class AllowAuthAbstractFactory implements AbstractFactoryInterface
{
    const KEY_ALLOW_AUTH = 'allowAuth';

    const KEY_CONFIG = 'config';

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
        return isset($config[static::KEY_ALLOW_AUTH][$requestedName]);
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
        $factoryConfig =$config[static::KEY_ALLOW_AUTH][$requestedName];

        if(!isset($factoryConfig[static::KEY_CONFIG])) {
            throw new ServiceNotCreatedException(static::KEY_CONFIG. " config not set.");
        }
        $allowConfig = $factoryConfig[static::KEY_CONFIG];
        $allowAuth = new AllowAuth($allowConfig);
        return $allowAuth;
    }
}