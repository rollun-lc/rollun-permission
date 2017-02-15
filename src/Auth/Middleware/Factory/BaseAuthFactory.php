<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 15.02.17
 * Time: 15:14
 */

namespace rollun\permission\Acl\Middleware\Factory;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use rollun\permission\Auth\Adapter\Factory\HttpFactory;
use rollun\permission\Auth\Middleware\BaseAuth;
use Zend\Authentication\Adapter\Http;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\FactoryInterface;

class BaseAuthFactory implements FactoryInterface
{
    const KEY_BASE_AUTH = 'baseAuth';

    const KEY_ADAPTER = 'adapter';

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
        if ($config[static::KEY_BASE_AUTH][static::KEY_ADAPTER] &&
            $container->has($config[static::KEY_BASE_AUTH][static::KEY_ADAPTER])
        ) {
            $adapter = $container->get($config[static::KEY_BASE_AUTH][static::KEY_ADAPTER]);
        }
        if (!isset($adapter)) {
            $adapterFactory = new HttpFactory();
            $adapter = $adapterFactory($container, Http::class);
        }
        return new BaseAuth($adapter);
    }
}
