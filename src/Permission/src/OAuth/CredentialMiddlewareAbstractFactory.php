<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\permission\OAuth;

use Interop\Container\ContainerInterface;
use InvalidArgumentException;
use Laminas\Diactoros\Response;
use Mezzio\Authentication\UserRepositoryInterface;
use Laminas\ServiceManager\Factory\AbstractFactoryInterface;

/**
 * Create instance of CredentialMiddleware using 'config' service from ContainerInterface
 *
 * Config example:
 * <code>
 *  [
 *      AbstractOAuthMiddlewareFactory::class => [
 *          CredentialMiddlewareAbstractFactory::class => [
 *              // ... override global config listed below
 *              'class' => LoginMiddleware::class,
 *              'userRepository' => 'userRepositoryServiceName', // default - UserRepositoryInterface::class
 *              'authorizedResponseFactory' => 'authorizedResponseFactoryServiceOrCallable',
 *              'unauthorizedResponseFactory' => 'unauthorizedResponseFactoryServiceOrCallable',
 *              'config' => [
 *
 *              ]
 *          ],
 *          AbstractOAuthMiddlewareFactory::KEY_OAUTH_CONFIG => [
 *              'googleClient' => 'googleClientServiceName', // default - GoogleClient::class
 *              'urlHelper' => 'urlHelper',
 *              'config' => [
 *                  'loginRouteName' => 'aouth-login',
 *                  'registerRouteName' => 'aouth-register',
 *                  // ...
 *              ]
 *          ]
 *      ]
 *  ]
 * </code>
 *
 * Class CredentialMiddlewareAbstractFactory
 * @package rollun\permission\Authentication\OAuth
 */
class CredentialMiddlewareAbstractFactory extends AbstractOAuthMiddlewareFactory implements AbstractFactoryInterface
{
    const KEY_USER_REPOSITORY = 'userRepository';

    const KEY_AUTHORIZE_RESPONSE_FACTORY = 'authorizedResponseFactory';

    const KEY_UN_AUTHORIZE_RESPONSE_FACTORY = 'unauthorizedResponseFactory';

    const KEY_CLASS = 'class';

    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return object|LoginMiddleware
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $serviceConfig = $this->getServiceConfig($container, $requestedName);

        if (isset($serviceConfig[self::KEY_USER_REPOSITORY])) {
            $userRepository = $container->get($serviceConfig[self::KEY_USER_REPOSITORY]);
        } else {
            $userRepository = $container->get(UserRepositoryInterface::class);
        }

        if (isset($serviceConfig[self::KEY_UN_AUTHORIZE_RESPONSE_FACTORY])) {
            $unauthorizedResponseFactory = $container->get($serviceConfig[self::KEY_UN_AUTHORIZE_RESPONSE_FACTORY]);
        } else {
            $unauthorizedResponseFactory = function () {
                return new Response();
            };
        }

        if (isset($serviceConfig[self::KEY_AUTHORIZE_RESPONSE_FACTORY])) {
            $authorizedResponseFactory = $container->get($serviceConfig[self::KEY_AUTHORIZE_RESPONSE_FACTORY]);
        } else {
            $authorizedResponseFactory = function () {
                return new Response();
            };
        }

        if (!isset($serviceConfig[self::KEY_CLASS])) {
            throw new InvalidArgumentException("Missing '" . self::KEY_CLASS . "' option");
        } elseif (!is_a($serviceConfig[self::KEY_CLASS], CredentialMiddleware::class, true)) {
            throw new InvalidArgumentException(
                'Expected instance of ' . CredentialMiddleware::class . ', ' . gettype($serviceConfig[self::KEY_CLASS])
                . ' given'
            );
        }

        $class = $serviceConfig[self::KEY_CLASS];
        $config = array_merge(
            $this->getGlobalConfig($container),
            $serviceConfig[self::KEY_CONFIG] ?? []
        );

        return new $class(
            $this->getGoogleClient($container, $requestedName),
            $userRepository,
            $this->getUrlHelper($container, $requestedName),
            $unauthorizedResponseFactory,
            $authorizedResponseFactory,
            $this->getLogger($container),
            $config
        );
    }

    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @return bool
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        return !empty($this->getServiceConfig($container, $requestedName));
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @return array
     */
    protected function getServiceConfig(ContainerInterface $container, $requestedName): array
    {
        return $container->get('config')[AbstractOAuthMiddlewareFactory::class][self::class][$requestedName] ?? [];
    }
}
