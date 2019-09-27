<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\permission\Authentication\Factory;

use Interop\Container\ContainerInterface;
use InvalidArgumentException;
use rollun\permission\Authentication\BasicAccess;
use Zend\Diactoros\Response;
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
 *              'class' => BasicAccess::class, // optional
 *              'realm' => 'realmValue', // required
 *              'userRepository' => 'userRepositoryService', // required
 *              'responseFactory' => 'responseFactoryServiceOrCallable', // optional
 *          ],
 *          'serviceRequestedName2' => [
 *              // ...
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
            $responseFactory = function () {
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
        }

        return new $class($userRepository, $realm, $responseFactory);
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
