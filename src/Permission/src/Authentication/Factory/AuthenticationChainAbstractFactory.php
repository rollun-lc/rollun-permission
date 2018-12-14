<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\permission\Authentication\Factory;

use Interop\Container\ContainerInterface;
use InvalidArgumentException;
use rollun\permission\Authentication\AuthenticationChain;
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
 *              'class' => AuthenticationServiceChain::class, // optional
 *              'authenticationServices' => 'authenticationServiceName1'
 *              'unauthorizedResponseFactory' => 'unauthorizedResponseFactoryServiceOrCallable', // optional
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
class AuthenticationChainAbstractFactory implements AbstractFactoryInterface
{
    const DEFAULT_CLASS = AuthenticationChain::class;

    const KEY_CLASS = 'class';

    const KEY_AUTHENTICATION_SERVICES = 'authenticationServices';

    const KEY_UNAUTHORIZED_RESPONSE_FACTORY = 'unauthorizedResponseFactory';

    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return AuthenticationChain
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $serviceConfig = $container->get('config')[self::class][$requestedName];

        if (!isset($serviceConfig[self::KEY_AUTHENTICATION_SERVICES])) {
            throw new InvalidArgumentException("Missing '" . self::KEY_AUTHENTICATION_SERVICES . "' option");
        }

        if (isset($serviceConfig[self::KEY_UNAUTHORIZED_RESPONSE_FACTORY])) {
            $unauthorizedResponseFactory = $serviceConfig[self::KEY_UNAUTHORIZED_RESPONSE_FACTORY];
        } else {
            $unauthorizedResponseFactory = function (ServerRequestInterface $request) {
                return new Response();
            };
        }

        $class = $serviceConfig[self::KEY_CLASS] ?? self::DEFAULT_CLASS;
        $authenticationServices = [];

        foreach ($serviceConfig[self::KEY_AUTHENTICATION_SERVICES] as $authenticationServiceName) {
            $authenticationServices[] = $container->get($authenticationServiceName);
        }

        if (!is_callable($unauthorizedResponseFactory) && $container->has($unauthorizedResponseFactory)) {
            $unauthorizedResponseFactory = $container->get($unauthorizedResponseFactory);
        }

        return new $class($authenticationServices, $unauthorizedResponseFactory);
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
