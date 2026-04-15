<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace rollun\permission\Authentication\Factory;

use Psr\Container\ContainerInterface;
use rollun\permission\Authentication\LcobucciJwtAccessTokenValidator;
use RuntimeException;

class LcobucciJwtAccessTokenValidatorFactory
{
    public function __invoke(ContainerInterface $container): LcobucciJwtAccessTokenValidator
    {
        $oauth2Config = $container->get('config')['oauth2'] ?? [];

        $publicKeyPath = $oauth2Config['public_key_path'] ?? null;
        if (!is_string($publicKeyPath) || $publicKeyPath === '') {
            throw new RuntimeException(
                'oauth2.public_key_path must be set (e.g. via OAUTH2_SERVER_PUBLIC_KEY_PATH env).'
            );
        }

        $expectedAudience = $oauth2Config['expected_audience'] ?? null;
        if (!is_string($expectedAudience) || $expectedAudience === '') {
            throw new RuntimeException(
                'oauth2.expected_audience must be a non-empty string '
                . '(set OAUTH2_EXPECTED_AUDIENCE env); JWT validator is security-critical '
                . 'and cannot operate without a known audience.'
            );
        }

        $expectedIssuer = $oauth2Config['expected_issuer'] ?? null;
        if ($expectedIssuer !== null && (!is_string($expectedIssuer) || $expectedIssuer === '')) {
            $expectedIssuer = null;
        }

        return new LcobucciJwtAccessTokenValidator($publicKeyPath, $expectedAudience, $expectedIssuer);
    }
}
