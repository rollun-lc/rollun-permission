<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\permission\Auth;

use Interop\Container\ContainerInterface;
use InvalidArgumentException;
use Zend\Diactoros\Response;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Create instance of AuthenticationServiceChain using 'config' service stored in ContainerInterface
 *
 * Config example
 *
 * <code>
 *  [
 *      AuthenticationServiceChainAbstractFactory::class => [
 *          'serviceRequestedName1' => [
 *              'class' => AuthenticationServiceChain::class, optional
 *              'authenticationServices' => 'authenticationServiceName1'
 *              'unauthorizedResponseFactory' => 'unauthorizedResponseFactoryServiceOrCallable', optional
 *          ],
 *          'serviceRequestedName2' => [
 *              'authenticationServices' => 'authenticationServiceName2'
 *          ],
 *          // ...
 *      ]
 *  ]
 * </code>
 *
 * Class AuthenticationServiceChainAbstractFactory
 * @package rollun\permission\Auth
 */
class AuthenticationServiceChainAbstractFactory implements AbstractFactoryInterface
{
    const KEY = self::class;

    const DEFAULT_CLASS = AuthenticationServiceChain::class;

    const KEY_CLASS = 'class';

    const KEY_AUTHENTICATION_SERVICE = 'authenticationServices';

    const KEY_UNAUTHORIZED_RESPONSE_FACTORY = 'unauthorizedResponseFactory';

    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return AuthenticationServiceChain
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $serviceConfig = $container->get('config')[self::class][$requestedName];

        if (!isset($serviceConfig[self::KEY_AUTHENTICATION_SERVICE])) {
            throw new InvalidArgumentException("Missing '" . self::KEY_AUTHENTICATION_SERVICE . "' option");
        }

        if (isset($serviceConfig[self::KEY_UNAUTHORIZED_RESPONSE_FACTORY])) {
            $unauthorizedResponseFactory = $serviceConfig[self::KEY_UNAUTHORIZED_RESPONSE_FACTORY];
        } else {
            $unauthorizedResponseFactory = function (ServerRequestInterface $request) {
                return new Response();
            };
        }

        $class = $serviceConfig[self::KEY_CLASS] ?? self::DEFAULT_CLASS;
        $authenticationService = $container->get($serviceConfig[self::KEY_AUTHENTICATION_SERVICE]);

        if (!is_callable($unauthorizedResponseFactory) && $container->has($unauthorizedResponseFactory)) {
            $unauthorizedResponseFactory = $container->get($unauthorizedResponseFactory);
        } else {
            throw new InvalidArgumentException("Invalid option '" . self::KEY_REALM . "'");
        }

        return new $class($authenticationService, $unauthorizedResponseFactory);
    }

    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @return bool
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        if (!($serviceConfig = $container->get('config')[self::class][$requestedName] ?? null)) {
            return false;
        }

        if ($class = $serviceConfig[self::KEY_CLASS] ?? null) {
            return is_a($class, self::DEFAULT_CLASS, true);
        }

        return true;
    }
}
