<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\permission\Authorization\Middleware\Factory;

use Interop\Container\ContainerInterface;
use rollun\permission\Authorization\Middleware\AccessForbiddenHandler;
use rollun\permission\Authorization\Middleware\AclMiddleware;
use Laminas\Permissions\Acl\Acl;
use Laminas\ServiceManager\Factory\FactoryInterface;

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
        $accessForbiddenHandler = $container->get(AccessForbiddenHandler::class);

        return new AclMiddleware($acl, $accessForbiddenHandler);
    }
}
