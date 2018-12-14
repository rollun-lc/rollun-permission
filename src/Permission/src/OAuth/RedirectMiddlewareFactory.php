<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\permission\OAuth;

use Interop\Container\ContainerInterface;

/**
 * Create instance of RedirectMiddleware using 'config' service from ContainerInterface
 *
 * Config example:
 * <code>
 *  [
 *      AbstractOAuthMiddlewareFactory::class => [
 *          RedirectMiddlewareAbstractFactory::class => [
 *              // ... override global config listed below
 *              'googleClient' => 'googleClientServiceName', // default - GoogleClient::class
 *              'config' => [
 *              ]
 *          ]
 *          AbstractOAuthMiddlewareFactory::KEY_OAUTH_CONFIG => [
 *              'googleClient' => 'googleClientServiceName', // default - GoogleClient::class
 *              'urlHelper' => 'urlHelper',
 *              'config' => [
 *                  'loginRouteName' => 'aouth-login',
 *                  'registerRouteName' => 'aouth-register',
 *                  'scopes' => 'email profile'
 *              ]
 *          ]
 *      ]
 *  ]
 * </code>
 *
 * Class RedirectMiddlewareAbstractFactory
 * @package rollun\permission\Authentication\OAuth
 */
class RedirectMiddlewareFactory extends AbstractOAuthMiddlewareFactory
{
    /**
     * @param ContainerInterface $container
     * @return RedirectMiddleware
     */
    public function __invoke(ContainerInterface $container)
    {
        $serviceConfig = $this->getServiceConfig($container);
        $config = array_merge(
            $this->getGlobalConfig($container),
            $serviceConfig[self::KEY_CONFIG] ?? []
        );

        return new RedirectMiddleware(
            $this->getGoogleClient($container, $serviceConfig),
            $this->getUrlHelper($container, $serviceConfig),
            $this->getLogger($container),
            $config
        );
    }

    /**
     * @param ContainerInterface $container
     * @return array
     */
    protected function getServiceConfig(ContainerInterface $container): array
    {
        return $container->get('config')[AbstractOAuthMiddlewareFactory::class][self::class] ?? [];
    }
}
