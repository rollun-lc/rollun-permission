<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\permission\Authentication\Factory;

use Interop\Container\ContainerInterface;
use InvalidArgumentException;
use Laminas\Diactoros\Response;
use Mezzio\Authentication\DefaultUser;
use Mezzio\Authentication\Session\PhpSession;
use Laminas\ServiceManager\Factory\AbstractFactoryInterface;

/**
 * Create instance of PhpSession using 'config' service stored in ContainerInterface
 *
 * Config example
 *
 * <code>
 *  [
 *      PhpSessionAbstractFactory::class => [
 *          'serviceRequestedName1' => [
 *              'config' => [
 *                  'username' => 'email', // optional, default - 'username'
 *                  'password' => 'passwd', // optional, default - 'password'
 *                  'redirect' => '/login', // required
 *              ],
 *              'userRepository' => 'userRepositoryService', // required
 *              'responseFactory' => 'responseFactoryServiceOrCallable', // optional
 *              'userFactory' => 'userFactoryServiceOrCallable', // optional
 *          ],
 *          'serviceRequestedName2' => [
 *              'authenticationServices' => 'authenticationServiceName2'
 *          ],
 *          // ...
 *      ]
 *  ]
 * </code>
 *
 * Class PhpSessionAbstractFactory
 * @package rollun\permission\Auth
 */
class PhpSessionAbstractFactory implements AbstractFactoryInterface
{
    const DEFAULT_CLASS = PhpSession::class;

    const KEY_CLASS = 'class';

    const KEY_CONFIG = 'config';

    const KEY_USER_REPOSITORY = 'userRepository';

    const KEY_USER_FACTORY = 'userFactory';

    const KEY_RESPONSE_FACTORY = 'responseFactory';

    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return PhpSession
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

        if (isset($serviceConfig[self::KEY_USER_FACTORY])) {
            $userFactory = $serviceConfig[self::KEY_USER_FACTORY];
        } else {
            $userFactory = function (string $identity, array $roles = [], array $details = []) {
                return new DefaultUser($identity, $roles, $details);
            };
        }

        if (!isset($serviceConfig[self::KEY_CONFIG])) {
            throw new InvalidArgumentException("Missing '" . self::KEY_CONFIG . "' option");
        }

        $class = $serviceConfig[self::KEY_CLASS] ?? self::DEFAULT_CLASS;
        $userRepository = $container->get($serviceConfig[self::KEY_USER_REPOSITORY]);
        $config = $serviceConfig[self::KEY_CONFIG];

        if (!is_callable($responseFactory) && $container->has($responseFactory)) {
            $responseFactory = $container->get($responseFactory);
        }

        if (!is_callable($userFactory) && $container->has($userFactory)) {
            $userFactory = $container->get($userFactory);
        }

        return new $class($userRepository, $config, $responseFactory, $userFactory);
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
