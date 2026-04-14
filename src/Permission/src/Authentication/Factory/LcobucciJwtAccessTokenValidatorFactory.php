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
        $config = $container->get('config');
        $publicKeyPath = $config['oauth2']['public_key_path'] ?? '/data/oauth/public.key';

        return new LcobucciJwtAccessTokenValidator($publicKeyPath);
    }
}
