<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 02.02.17
 * Time: 15:11
 */

namespace rollun\permission\Auth\Middleware\Factory;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use rollun\permission\Auth\Adapter\Session;
use rollun\permission\Auth\Middleware\LogoutAction;
use Zend\Authentication\Storage\Session as SessionStorage;
use Zend\Authentication\AuthenticationService;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\Session\Service\SessionManagerFactory;
use Zend\Session\SessionManager;

class LogoutActionFactory implements FactoryInterface
{
    const KEY_AUTHENTICATION_SERVICE = 'authenticationService';
    const KEY_LOGOUT = 'logout';

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
        $factoryConfig = isset($config[static::KEY_LOGOUT]) ? $config[static::KEY_LOGOUT] : [];
        if (isset($factoryConfig[static::KEY_AUTHENTICATION_SERVICE]) &&
            $container->has($factoryConfig[static::KEY_AUTHENTICATION_SERVICE])
        ) {
            $authService = $container->get($factoryConfig[static::KEY_AUTHENTICATION_SERVICE]);
        } else if ($container->has(AuthenticationService::class)) {
            $authService = $container->get(AuthenticationService::class);
        } else {
            $sessionFactory = new SessionManagerFactory();
            $sessionManager = $sessionFactory($container, SessionManager::class);
            $authStorage = new SessionStorage(Session::DEFAULT_SESSION_NAMESPACE, Session::DEFAULT_SESSION_MEMBER, $sessionManager);
            $authService = new AuthenticationService($authStorage);
        }
        return new LogoutAction($authService);
    }
}
