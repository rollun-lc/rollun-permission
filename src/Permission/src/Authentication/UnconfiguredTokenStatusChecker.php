<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace rollun\permission\Authentication;

class UnconfiguredTokenStatusChecker implements TokenStatusCheckerInterface
{
    public function checkAccessToken(BearerTokenClaims $claims): void
    {
        throw new TokenStatusCheckerNotConfiguredException(
            'TokenStatusCheckerInterface must be configured in the application container.'
        );
    }
}

