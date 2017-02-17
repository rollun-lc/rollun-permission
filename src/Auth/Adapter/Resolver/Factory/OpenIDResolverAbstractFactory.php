<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 02.02.17
 * Time: 15:47
 */

namespace rollun\permission\Auth\Adapter\Resolver\Factory;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use rollun\api\Api\Google\Client\Web;
use rollun\permission\Auth\Adapter\OpenIDAdapter;
use rollun\permission\Auth\Adapter\Resolver\OpenIDResolver;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class OpenIDResolverAbstractFactory implements AbstractFactoryInterface
{

    const KEY_RESOLVER = 'openIdResolver';

    const KEY_USER_DS_SERVICE = 'userDataStoreService';

    const KEY_WEB_SERVICE = 'webService';

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

        if (!isset($resolverConfig[static::KEY_USER_DS_SERVICE])
            || !$container->has($resolverConfig[static::KEY_USER_DS_SERVICE])
        ) {
            throw new ServiceNotFoundException(
                $resolverConfig[static::KEY_USER_DS_SERVICE] . " not found."
            );
        }
        $webService = isset($resolverConfig[static::KEY_WEB_SERVICE]) ?
            $resolverConfig[static::KEY_WEB_SERVICE] : Web::class;
        $webClient = $container->get($webService);
        $dataStore = $container->get($resolverConfig[static::KEY_USER_DS_SERVICE]);
        return new OpenIDResolver($webClient, $dataStore);
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
        return isset($config[static::KEY_RESOLVER][$requestedName]);
    }
}
