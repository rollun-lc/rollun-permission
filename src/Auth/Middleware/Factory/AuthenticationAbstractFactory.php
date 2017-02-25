<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 15.02.17
 * Time: 15:14
 */

namespace rollun\permission\Auth\Middleware\Factory;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use rollun\permission\Auth\Adapter\Factory\HttpAdapterFactory;
use rollun\permission\Auth\Middleware\AbstractAuthenticationAction;
use rollun\permission\Auth\Middleware\LazyAuthenticationAction;
use Zend\Authentication\Adapter\Http;
use Zend\Authentication\AuthenticationService;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\Authentication\Storage\Session as SessionStorage;
use Zend\Session\Service\SessionManagerFactory;
use Zend\Session\SessionManager;

class AuthenticationAbstractFactory implements AbstractFactoryInterface
{
    const KEY_AUTHENTICATION = 'authentication';

    const KEY_ADAPTER = 'adapter';

    const KEY_AUTHENTICATION_SERVICE = 'authenticationService';

    const KEY_CLASS = 'class';

    const EXTENDED_CLASS = AbstractAuthenticationAction::class;

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
        $factoryConfig = $config[static::KEY_AUTHENTICATION][$requestedName];
        if (isset($factoryConfig[static::KEY_ADAPTER]) &&
            $container->has($factoryConfig[static::KEY_ADAPTER])
        ) {
            $adapter = $container->get($factoryConfig[static::KEY_ADAPTER]);
        } else {
            throw new ServiceNotFoundException($factoryConfig[static::KEY_ADAPTER] . " service not found.");
        }


        if (isset($factoryConfig[static::KEY_AUTHENTICATION_SERVICE]) &&
            $container->has($factoryConfig[static::KEY_AUTHENTICATION_SERVICE])
        ) {
            $authService = $container->get($factoryConfig[static::KEY_AUTHENTICATION_SERVICE]);
        } else if ($container->has(AuthenticationService::class)) {
            $authService = $container->get(AuthenticationService::class);
        } else {
            $sessionFactory = new SessionManagerFactory();
            $sessionManager = $sessionFactory($container, SessionManager::class);
            $authStorage = new SessionStorage('ZendAuth', 'session', $sessionManager);
            $authService = new AuthenticationService($authStorage);
        }
        $class = $factoryConfig[static::KEY_CLASS];
        return new $class($adapter, $authService);
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
        return isset($config[static::KEY_AUTHENTICATION][$requestedName]) &&
            isset($config[static::KEY_AUTHENTICATION][$requestedName][static::KEY_CLASS]) &&
            is_a($config[static::KEY_AUTHENTICATION][$requestedName][static::KEY_CLASS], static::EXTENDED_CLASS);
    }
}
