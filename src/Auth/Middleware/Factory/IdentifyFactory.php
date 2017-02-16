<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 02.02.17
 * Time: 17:38
 */

namespace rollun\permission\Acl\Middleware\Factory;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use rollun\permission\Acl\Middleware\RoleResolver;
use rollun\permission\Auth\Middleware\IdentifyAction;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\AuthenticationServiceInterface;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\Authentication\Storage\Session as SessionStorage;
use Zend\Session\Service\SessionManagerFactory;
use Zend\Session\SessionManager;

class IdentifyFactory implements FactoryInterface
{

    const KEY_IDENTIFY = 'identify';

    const KEY_AUTHENTICATION_SERVICE = 'authenticationService';

    /** @var  AuthenticationServiceInterface */
    protected $authService;

    /**
     * Create an object
     *
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return object
     * @throws \Exception
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');

        if (isset($config[static::KEY_AUTHENTICATION_SERVICE]) &&
            $container->has($config[static::KEY_AUTHENTICATION_SERVICE])
        ) {
            $authService = $container->get($config[static::KEY_AUTHENTICATION_SERVICE]);
        } else if ($container->has(AuthenticationService::class)) {
            $authService = $container->get(AuthenticationService::class);
        } else {
            $sessionFactory = new SessionManagerFactory();
            $sessionManager = $sessionFactory($container, SessionManager::class);
            $authStorage = new SessionStorage('ZendAuth', 'session', $sessionManager);
            $authService = new AuthenticationService($authStorage);
        }
        return new IdentifyAction($authService);
    }
}
