<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace rollun\permission\Middleware\Factory;

use Psr\Container\ContainerInterface;
use rollun\permission\Authentication\BearerTokenAuthenticator;
use rollun\permission\Middleware\BearerTokenAuthenticationMiddleware;

class BearerTokenAuthenticationMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): BearerTokenAuthenticationMiddleware
    {
        return new BearerTokenAuthenticationMiddleware(
            $container->get(BearerTokenAuthenticator::class)
        );
    }
}

