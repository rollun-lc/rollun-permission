<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\permission\Acl\Middleware\Factory;

use Interop\Container\ContainerInterface;
use rollun\permission\Acl\Middleware\RoleResolver;
use Zend\ServiceManager\Factory\FactoryInterface;

class RoleResolverFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return object|RoleResolver
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new RoleResolver();
    }
}
