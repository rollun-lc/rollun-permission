<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace rollun\permission\Authentication;

use DateTimeZone;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Token\RegisteredClaims;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\StrictValidAt;
use Throwable;

use function array_filter;
use function array_map;
use function array_shift;
use function array_values;
use function is_array;
use function is_string;
use function preg_split;
use function trim;

class LcobucciJwtAccessTokenValidator implements JwtAccessTokenValidatorInterface
{
    private string $publicKeyPath;

    public function __construct(string $publicKeyPath)
    {
        $this->publicKeyPath = $publicKeyPath;
    }

    public function validate(string $jwtToken): BearerTokenClaims
    {
        if (!is_readable($this->publicKeyPath)) {
            throw new BearerTokenAuthenticationException('OAuth2 public key is not readable.');
        }

        $configuration = Configuration::forAsymmetricSigner(
            new Sha256(),
            InMemory::empty(),
            InMemory::file($this->publicKeyPath)
        );

        try {
            $token = $configuration->parser()->parse($jwtToken);
        } catch (Throwable $e) {
            throw new BearerTokenAuthenticationException('Access token format is invalid.', 0, $e);
        }

        if (!$token instanceof Plain) {
            throw new BearerTokenAuthenticationException('Access token format is invalid.');
        }

        $constraints = [
            new SignedWith($configuration->signer(), $configuration->verificationKey()),
            new StrictValidAt(new SystemClock(new DateTimeZone('UTC'))),
        ];

        if (!$configuration->validator()->validate($token, ...$constraints)) {
            throw new BearerTokenAuthenticationException('Access token signature or claims are invalid.');
        }

        $subject = $token->claims()->get(RegisteredClaims::SUBJECT, null);
        $tokenId = $token->claims()->get(RegisteredClaims::ID, null);
        $audience = $this->extractAudience($token->claims()->get(RegisteredClaims::AUDIENCE, null));

        if (!is_string($subject) || $subject === '') {
            throw new BearerTokenAuthenticationException('Access token does not contain valid "sub" claim.');
        }

        if (!is_string($tokenId) || $tokenId === '') {
            throw new BearerTokenAuthenticationException('Access token does not contain valid "jti" claim.');
        }

        if ($audience === '') {
            throw new BearerTokenAuthenticationException('Access token does not contain valid "aud" claim.');
        }

        return new BearerTokenClaims(
            $subject,
            $audience,
            $tokenId,
            $this->extractScopes($token->claims()->get('scopes', []))
        );
    }

    private function extractAudience($audienceClaim): string
    {
        if (is_string($audienceClaim)) {
            return $audienceClaim;
        }

        if (!is_array($audienceClaim) || $audienceClaim === []) {
            return '';
        }

        $audience = array_shift($audienceClaim);
        return is_string($audience) ? $audience : '';
    }

    /**
     * @param mixed $scopesClaim
     * @return string[]
     */
    private function extractScopes($scopesClaim): array
    {
        if (is_string($scopesClaim)) {
            return array_values(array_filter(preg_split('/\s+/', trim($scopesClaim)) ?: []));
        }

        if (!is_array($scopesClaim)) {
            return [];
        }

        return array_values(array_filter(array_map('strval', $scopesClaim), static function (string $scope): bool {
            return $scope !== '';
        }));
    }
}
