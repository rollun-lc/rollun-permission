<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace rollun\permission\Authentication\Factory;

use Psr\Container\ContainerInterface;
use rollun\permission\Authentication\BearerTokenAuthenticator;
use rollun\permission\Authentication\JwtAccessTokenValidatorInterface;
use rollun\permission\Authentication\TokenStatusCheckerInterface;
use rollun\permission\Authentication\UserRolesResolver;
use rollun\permission\UserProvider\UserProviderChain;

class BearerTokenAuthenticatorFactory
{
    public function __invoke(ContainerInterface $container): BearerTokenAuthenticator
    {
        return new BearerTokenAuthenticator(
            $container->get(JwtAccessTokenValidatorInterface::class),
            $container->get(TokenStatusCheckerInterface::class),
            $container->get(UserProviderChain::class),
            $container->get(UserRolesResolver::class)
        );
    }
}
