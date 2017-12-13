<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 16.02.17
 * Time: 15:26
 */

namespace rollun\permission\Auth\Adapter\Factory;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use rollun\permission\Auth\Adapter\AbstractWebAdapter;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;

class AuthAdapterAbstractFactory implements AbstractFactoryInterface
{
    const KEY = 'authAdapter';

    const KEY_ADAPTER_CONFIG = 'config';

    const KEY_AC_REALM = 'realm';

    const KEY_CLASS = 'class';

    const EXTENDED_CLASS = AbstractWebAdapter::class;

    const DEFAULT_REALM = 'RollunService';

    /**
     * Can the factory create an instance for the service?
     *
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @return bool
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        try {
            $config = $container->get('config');
        } catch (NotFoundExceptionInterface | ContainerExceptionInterface $e) {
            return false;
        }
        return isset($config[static::KEY][$requestedName]) &&
            isset($config[static::KEY][$requestedName][static::KEY_CLASS]) &&
            is_a($config[static::KEY][$requestedName][static::KEY_CLASS], static::EXTENDED_CLASS, true);
    }

    /**
     * Create an object
     *
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return object
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');
        $factoryConfig = $config[static::KEY][$requestedName];

        $class = $factoryConfig[static::KEY_CLASS];

        if (!isset($factoryConfig[static::KEY_CLASS])) {
            throw new ServiceNotCreatedException("Config not set");
        }
        $adapterConfig = isset($factoryConfig[static::KEY_ADAPTER_CONFIG]) ? $factoryConfig[static::KEY_ADAPTER_CONFIG] : [];

        /** @var AbstractWebAdapter $adapter */
        $adapter = new $class($adapterConfig);

        return $adapter;
    }
}
