<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace rollun\permission\Authentication\Factory;

use Psr\Container\ContainerInterface;
use rollun\permission\Authentication\UserRolesResolver;
use rollun\permission\ConfigProvider;

class UserRolesResolverFactory
{
    public function __invoke(ContainerInterface $container): UserRolesResolver
    {
        $config = $container->get('config')[UserRepositoryFactory::class][UserRepositoryFactory::KEY_CONFIG] ?? [];

        return new UserRolesResolver(
            $container->get(ConfigProvider::USER_ROLE_DATASTORE_SERVICE),
            $container->get(ConfigProvider::ROLE_DATASTORE_SERVICE),
            $config
        );
    }
}

