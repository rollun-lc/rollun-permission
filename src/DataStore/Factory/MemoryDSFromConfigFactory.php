<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 01.02.17
 * Time: 12:03
 */

namespace rollun\permission\DataStore\Factory;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use rollun\datastore\DataStore\Factory\DataStoreAbstractFactory;
use rollun\permission\DataStore\MemoryConfig;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class MemoryDSFromConfigFactory extends DataStoreAbstractFactory
{

    const KEY_CONFIG = 'config';

    protected static $KEY_DATASTORE_CLASS = MemoryConfig::class;

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
        $serviceConfig = $config[self::KEY_DATASTORE][$requestedName];
        $requestedClassName = $serviceConfig[self::KEY_CLASS];
        $confName = $serviceConfig[static::KEY_CONFIG];
        $this::$KEY_IN_CREATE = 0;
        return new $requestedClassName($config[$confName]);
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
        if (static::$KEY_IN_CANCREATE || static::$KEY_IN_CREATE) {
            return false;
        }
        static::$KEY_IN_CANCREATE = 1;
        $config = $container->get('config');
        if (!isset($config[static::KEY_DATASTORE][$requestedName][static::KEY_CLASS]) &&
            !isset($config[static::KEY_DATASTORE][$requestedName][static::KEY_CONFIG])
        ) {
            $result = false;
        } else {
            $requestedClassName = $config[static::KEY_DATASTORE][$requestedName][static::KEY_CLASS];
            $result = is_a($requestedClassName, static::$KEY_DATASTORE_CLASS, true);
        }
        $this::$KEY_IN_CANCREATE = 0;
        return $result;
    }
}
