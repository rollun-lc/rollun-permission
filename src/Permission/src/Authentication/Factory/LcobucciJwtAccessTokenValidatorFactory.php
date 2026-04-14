<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace rollun\permission\Authentication\Factory;

use Psr\Container\ContainerInterface;
use rollun\permission\Authentication\LcobucciJwtAccessTokenValidator;

class LcobucciJwtAccessTokenValidatorFactory
{
    public function __invoke(ContainerInterface $container): LcobucciJwtAccessTokenValidator
    {
        $publicKeyPath = getenv('OAUTH2_SERVER_PUBLIC_KEY_PATH') ?: '/data/oauth/public.key';

        return new LcobucciJwtAccessTokenValidator($publicKeyPath);
    }
}

