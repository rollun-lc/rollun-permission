<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 16.02.17
 * Time: 12:43
 */

namespace rollun\permission\Auth\Middleware\Factory;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use rollun\permission\Auth\Middleware\UserResolver;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\FactoryInterface;

class UserResolverFactory implements FactoryInterface
{

    const KEY_USER_RESOLVER = 'userResolver';

    const KEY_USER_ROLES_DS_SERVICE = 'userRolesDataStoreService';

    const KEY_ROLES_DS_SERVICE = 'rolesDataStoreService';

    const KEY_USER_DS_SERVICE = 'usersDataStoreService';

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
        $factoryConfig = $config[static::KEY_USER_RESOLVER];
        if (isset($config[static::KEY_USER_RESOLVER])) {
            if (!isset($factoryConfig[static::KEY_USER_DS_SERVICE]) ||
                !$container->has($factoryConfig[static::KEY_USER_DS_SERVICE])
            ) {
                throw new ServiceNotFoundException('User DataStore Service not found.');
            }
            if (!isset($factoryConfig[static::KEY_USER_ROLES_DS_SERVICE]) ||
                !$container->has($factoryConfig[static::KEY_USER_ROLES_DS_SERVICE])
            ) {
                throw new ServiceNotFoundException('UserRoles DataStore Service not found.');
            }
            if (!isset($factoryConfig[static::KEY_ROLES_DS_SERVICE]) ||
                !$container->has($factoryConfig[static::KEY_ROLES_DS_SERVICE])
            ) {
                throw new ServiceNotFoundException('UserRoles DataStore Service not found.');
            }
            $userDS = $container->get($factoryConfig[static::KEY_USER_DS_SERVICE]);
            $rolesDS = $container->get($factoryConfig[static::KEY_ROLES_DS_SERVICE]);
            $userRolesDS = $container->get($factoryConfig[static::KEY_USER_ROLES_DS_SERVICE]);
            $userResolver = new UserResolver($userDS, $rolesDS, $userRolesDS);
            return $userResolver;
        }
        throw new ServiceNotFoundException('Config not found.');
    }
}
