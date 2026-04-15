<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace rollun\permission\Authentication\Factory;

use Psr\Container\ContainerInterface;
use rollun\permission\Authentication\DataStoreTokenStatusChecker;
use rollun\permission\ConfigProvider;

class DataStoreTokenStatusCheckerFactory
{
    public function __invoke(ContainerInterface $container): DataStoreTokenStatusChecker
    {
        return new DataStoreTokenStatusChecker(
            $container->get(ConfigProvider::OAUTH_ACCESS_TOKENS_DATASTORE_SERVICE),
            $container->get(ConfigProvider::OAUTH_CLIENTS_DATASTORE_SERVICE)
        );
    }
}
