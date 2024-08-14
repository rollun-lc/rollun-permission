<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\permission\Authentication\Factory;

use Psr\Container\ContainerInterface;
use rollun\permission\Authentication\GuestAuthentication;
use Laminas\Diactoros\Response;
use Mezzio\Authentication\DefaultUser;

/**
 * Create instance of GuestAuthentication using 'config' service stored in ContainerInterface
 *
 * Config example
 *
 * <code>
 *  [
 *      GuestAuthenticationFactory::class => [
 *          'responseFactory' => 'responseFactoryServiceOrCallable', // optional
 *          'userFactory' => 'userFactoryServiceOrCallable', // optional
 *      ]
 *  ]
 * </code>
 *
 * Class GuestAuthenticationFactory
 * @package rollun\permission\Authentication\Factory
 */
class GuestAuthenticationFactory
{
    const KEY_RESPONSE_FACTORY = 'responseFactory';

    const KEY_USER_FACTORY = 'userFactory';

    /**
     * @param ContainerInterface $container
     * @return GuestAuthentication
     */
    public function __invoke(ContainerInterface $container)
    {
        $serviceConfig = $container->get('config')[self::class] ?? [];

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

        return new GuestAuthentication($userFactory, $responseFactory);
    }
}
