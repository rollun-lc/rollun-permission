<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 20.02.17
 * Time: 14:12
 */

namespace rollun\permission\Acl\DataSource\Factory;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use rollun\permission\Acl\DataSource\MemoryConfig;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;

class ConfigDataSourceAbstractFactory implements AbstractFactoryInterface
{

    const KEY_DATASOURCE = 'dataSource';

    const KEY_CONFIG = 'config';

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
        $serviceConfig = $config[self::KEY_DATASOURCE][$requestedName];
        $confName = isset($serviceConfig[static::KEY_CONFIG]) ? $serviceConfig[static::KEY_CONFIG] : $requestedName;
        $data = $config[$confName];
        return new MemoryConfig($data);
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
        return isset($config[static::KEY_DATASOURCE][$requestedName]);
    }
}
