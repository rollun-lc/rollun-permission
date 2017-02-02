<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 02.02.17
 * Time: 15:00
 */

namespace rollun\permission\Auth\Middleware\Factory;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use rollun\permission\Api\Google\Client\OpenID;
use rollun\permission\Auth\Middleware\LoginAction;
use rollun\permission\Auth\OpenIDAuthManager;
use Zend\Expressive\Helper\UrlHelper;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\Session\Container as SessionStorage;
use Zend\Session\SessionManager;

class LoginActionFactory implements FactoryInterface
{

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
        $sessionManager = $container->get(SessionManager::class);
        $authStorage = new SessionStorage('ZendAuth', 'session', $sessionManager);
        $authManager = $container->get(OpenIDAuthManager::class);
        $googleClient = $container->get('OpenIDAuthClient');
        $urlHelper = $container->get(UrlHelper::class);

        return new LoginAction($authStorage, $authManager, $googleClient, $urlHelper);
    }
}
