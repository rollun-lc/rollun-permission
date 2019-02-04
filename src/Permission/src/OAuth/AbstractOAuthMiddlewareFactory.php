<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\permission\OAuth;

use Interop\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Zend\Expressive\Helper\UrlHelper;

abstract class AbstractOAuthMiddlewareFactory
{
    const KEY_OAUTH_CONFIG = 'oAuthConfig';

    const KEY_CONFIG = 'config';

    const KEY_GOOGLE_CLIENT = 'googleClient';

    const KEY_URL_HELPER  = 'urlHelper';

    /**
     * @param ContainerInterface $container
     * @return LoggerInterface
     */
    protected function getLogger(ContainerInterface $container): LoggerInterface
    {
        return $container->get(LoggerInterface::class);
    }

    /**
     * @param ContainerInterface $container
     * @return array
     */
    protected function getGlobalConfig(ContainerInterface $container)
    {
        return $container->get('config')[AbstractOAuthMiddlewareFactory::class][self::KEY_OAUTH_CONFIG] ?? [];
    }

    /**
     * @param ContainerInterface $container
     * @param $serviceConfig
     * @return GoogleClient
     */
    protected function getGoogleClient(ContainerInterface $container, $serviceConfig): GoogleClient
    {
        if (isset($serviceConfig[self::KEY_GOOGLE_CLIENT])) {
            return $container->get($serviceConfig[self::KEY_GOOGLE_CLIENT]);
        } else {
            return $container->get(GoogleClient::class);
        }
    }

    /**
     * @param ContainerInterface $container
     * @param $serviceConfig
     * @return UrlHelper
     */
    protected function getUrlHelper(ContainerInterface $container, $serviceConfig): UrlHelper
    {
        if (isset($serviceConfig[self::KEY_GOOGLE_CLIENT])) {
            return $container->get($serviceConfig[self::KEY_GOOGLE_CLIENT]);
        } else {
            return $container->get(UrlHelper::class);
        }
    }
}
