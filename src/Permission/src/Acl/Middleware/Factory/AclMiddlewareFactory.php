<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\permission\Acl\Middleware\Factory;

use Interop\Container\ContainerInterface;
use rollun\permission\Acl\Middleware\AclMiddleware;
use Zend\Permissions\Acl\Acl;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Create instance of AclMiddleware
 *
 * Class AclMiddlewareFactory
 * @package rollun\permission\Acl\Middleware\Factory
 */
class AclMiddlewareFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return AclMiddleware
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $acl = $container->get(Acl::class);

        return new AclMiddleware($acl);
    }
}
