<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace rollun\permission\Authentication;

use RuntimeException;

class BearerTokenAuthenticationException extends RuntimeException
{
    public const ERROR_CODE = 'invalid_token';
}

