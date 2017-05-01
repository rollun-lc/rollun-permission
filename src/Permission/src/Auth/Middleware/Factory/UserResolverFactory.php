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
use rollun\permission\Acl\Factory\AclFromDataStoreFactory;
use rollun\permission\Auth\Middleware\UserResolver;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\FactoryInterface;

class UserResolverFactory implements FactoryInterface
{

    const KEY = 'userResolver';

    const KEY_USER_ROLES_DS_SERVICE = 'userRolesDataStoreService';

    const KEY_ROLES_DS_SERVICE = 'rolesDataStoreService';

    const KEY_USER_DS_SERVICE = 'usersDataStoreService';

    const DEFAULT_USER_DS = 'userDS';

    const DEFAULT_USER_ROLES_DS = 'userRolesDS';

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
        $factoryConfig = $config[static::KEY];

        $userDS = isset($factoryConfig[static::KEY_USER_DS_SERVICE]) ?
            $factoryConfig[static::KEY_USER_DS_SERVICE] :
            static::DEFAULT_USER_DS;
        $userRolesDS = isset($factoryConfig[static::KEY_USER_ROLES_DS_SERVICE]) ?
            $factoryConfig[static::KEY_USER_ROLES_DS_SERVICE] :
            static::DEFAULT_USER_ROLES_DS;
        $rolesDS = isset($factoryConfig[static::KEY_ROLES_DS_SERVICE]) ?
            $factoryConfig[static::KEY_ROLES_DS_SERVICE] :
            AclFromDataStoreFactory::DEFAULT_ROLES_DS;

        if (!$container->has($userDS) ){
            throw new ServiceNotFoundException('User DataStore Service not found.');
        }

        if (!$container->has($userRolesDS)) {
            throw new ServiceNotFoundException('UserRoles DataStore Service not found.');
        }

        if (!$container->has($rolesDS)) {
            throw new ServiceNotFoundException('Roles DataStore Service not found.');
        }

        $userDS = $container->get($userDS);
        $rolesDS = $container->get($rolesDS);
        $userRolesDS = $container->get($userRolesDS);
        $userResolver = new UserResolver($userDS, $rolesDS, $userRolesDS);
        return $userResolver;
    }
}
