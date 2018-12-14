<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\permission;

use InvalidArgumentException;
use Psr\Container\ContainerInterface;

/**
 * Create PermissionMiddleware instance using 'config' service from ContainerInterface
 *
 * Config example:
 * <code>
 *  [
 *      PermissionMiddlewareFactory::class => [
 *          'aclAuthentication' => 'aclErrorHandlerServiceName'
 *          'aclAuthorization' => 'aclErrorHandlerServiceName'
 *      ]
 *  ]
 * </code>
 *
 * Class PermissionMiddlewareFactory
 * @package rollun\permission
 */
class PermissionMiddlewareFactory
{
    const KEY_ACL_AUTHENTICATION = 'aclAuthentication';

    const KEY_ACL_AUTHORIZATION = 'aclAuthorization';

    /**
     * @param ContainerInterface $container
     * @return PermissionMiddleware
     */
    public function __invoke(ContainerInterface $container)
    {
        $serviceConfig = $container->get('config')[self::class] ?? null;

        if (is_null($serviceConfig)) {
            throw new InvalidArgumentException('Missing config for ' . PermissionMiddlewareFactory::class . ' factory');
        }

        if (!isset($serviceConfig[self::KEY_ACL_AUTHENTICATION])) {
            throw new InvalidArgumentException("Missing '" . self::KEY_ACL_AUTHENTICATION . "' option");
        }

        if (!isset($serviceConfig[self::KEY_ACL_AUTHORIZATION])) {
            throw new InvalidArgumentException("Missing '" . self::KEY_ACL_AUTHORIZATION . "' option");
        }

        $middlewares[] = $container->get($serviceConfig[self::KEY_ACL_AUTHENTICATION]);
        $middlewares[] = $container->get($serviceConfig[self::KEY_ACL_AUTHORIZATION]);

        return new PermissionMiddleware($middlewares);
    }
}
