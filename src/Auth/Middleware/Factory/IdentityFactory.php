<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 01.03.17
 * Time: 19:52
 */

namespace rollun\permission\Auth\Middleware\Factory;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use rollun\permission\Auth\Adapter\AbstractWebAdapter;
use rollun\permission\Auth\Adapter\Interfaces\IdentityAdapterInterface;
use rollun\permission\Auth\Middleware\IdentityAction;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\FactoryInterface;

class IdentityFactory implements FactoryInterface
{

    const KEY_IDENTITY = 'identity';

    const KEY_ADAPTERS_SERVICE = 'adapters';

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
        if (!isset($config[static::KEY_IDENTITY][static::KEY_ADAPTERS_SERVICE])) {
            throw new ServiceNotCreatedException("Config not found.");
        }
        $adaptersService = $config[static::KEY_IDENTITY][static::KEY_ADAPTERS_SERVICE];
        $adapters = [];
        foreach ($adaptersService as $adapterService) {
            if (!$container->has($adapterService)) {
                throw new ServiceNotFoundException($adapterService . " not found service.");
            }
            $adapter = $container->has($adapterService);
            if (!is_a($adapter, AbstractWebAdapter::class, true) ||
                !is_a($adapter, IdentityAdapterInterface::class, true)
            ) {
                throw new ServiceNotCreatedException(
                    "$adapterService is not instance of " .
                    AbstractWebAdapter::class . "or implement " . IdentityAdapterInterface::class . "."
                );
            }
            $adapters[] = $adapter;
        }
        return new IdentityAction($adapters);
    }
}
