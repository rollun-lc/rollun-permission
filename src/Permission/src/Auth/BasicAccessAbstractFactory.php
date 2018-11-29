<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\permission\Auth;

use Interop\Container\ContainerInterface;
use InvalidArgumentException;
use Zend\Diactoros\Response;
use Zend\Expressive\Authentication\Basic\BasicAccess;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Create instance of BasicAccess using 'config' service stored in ContainerInterface
 *
 * Config example
 *
 * <code>
 *  [
 *      BasicAccessAbstractFactory::class => [
 *          'serviceRequestedName1' => [
 *              'realm' => 'realmValue',
 *              'userRepository' => 'userRepositoryService'
 *              'responseFactory' => 'responseFactoryServiceOrCallable', optional
 *          ],
 *          'serviceRequestedName2' => [
 *              'authenticationServices' => 'authenticationServiceName2'
 *          ],
 *          // ...
 *      ]
 *  ]
 * </code>
 *
 * Class BasicAccessAbstractFactory
 * @package rollun\permission\Auth
 */
class BasicAccessAbstractFactory implements AbstractFactoryInterface
{
    const KEY = self::class;

    const DEFAULT_CLASS = BasicAccess::class;

    const KEY_CLASS = 'class';

    const KEY_REALM = 'realm';

    const KEY_USER_REPOSITORY = 'userRepository';

    const KEY_RESPONSE_FACTORY = 'responseFactory';

    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return BasicAccess
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $serviceConfig = $container->get('config')[self::class][$requestedName];

        if (!isset($serviceConfig[self::KEY_USER_REPOSITORY])) {
            throw new InvalidArgumentException("Missing '" . self::KEY_USER_REPOSITORY . "' option");
        }

        if (isset($serviceConfig[self::KEY_RESPONSE_FACTORY])) {
            $responseFactory = $serviceConfig[self::KEY_RESPONSE_FACTORY];
        } else {
            $responseFactory = function (ServerRequestInterface $request) {
                return new Response();
            };
        }

        if (!isset($serviceConfig[self::KEY_REALM])) {
            throw new InvalidArgumentException("Missing '" . self::KEY_REALM . "' option");
        }

        $class = $serviceConfig[self::KEY_CLASS] ?? self::DEFAULT_CLASS;
        $userRepository = $container->get($serviceConfig[self::KEY_USER_REPOSITORY]);
        $realm = $serviceConfig[self::KEY_REALM];

        if (!is_callable($responseFactory) && $container->has($responseFactory)) {
            $responseFactory = $container->get($responseFactory);
        } else {
            throw new InvalidArgumentException("Invalid option '" . self::KEY_REALM . "'");
        }

        return $class($userRepository, $realm, $responseFactory);
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
