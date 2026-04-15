<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace rollun\permission\Authentication;

use DateTimeZone;
use InvalidArgumentException;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Token\RegisteredClaims;
use Lcobucci\JWT\Validation\Constraint;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\PermittedFor;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\StrictValidAt;
use Throwable;

use function array_filter;
use function array_map;
use function array_values;
use function is_array;
use function is_readable;
use function is_string;
use function preg_split;
use function trim;

class LcobucciJwtAccessTokenValidator implements JwtAccessTokenValidatorInterface
{
    private string $publicKeyPath;
    private string $expectedAudience;
    private ?string $expectedIssuer;
    private Configuration $configuration;
    /** @var Constraint[] */
    private array $constraints;

    public function __construct(
        string $publicKeyPath,
        string $expectedAudience,
        ?string $expectedIssuer = null
    ) {
        if (trim($expectedAudience) === '') {
            throw new InvalidArgumentException(
                'Expected audience must be a non-empty string; '
                . 'JWT validator cannot operate without a known audience.'
            );
        }

        if (!is_readable($publicKeyPath)) {
            throw new BearerTokenAuthenticationException('OAuth2 public key is not readable.');
        }

        $this->publicKeyPath = $publicKeyPath;
        $this->expectedAudience = $expectedAudience;
        $this->expectedIssuer = $expectedIssuer;

        $this->configuration = Configuration::forAsymmetricSigner(
            new Sha256(),
            InMemory::empty(),
            InMemory::file($this->publicKeyPath)
        );

        $this->constraints = [
            new SignedWith($this->configuration->signer(), $this->configuration->verificationKey()),
            new StrictValidAt(new SystemClock(new DateTimeZone('UTC'))),
            new PermittedFor($this->expectedAudience),
        ];

        if ($this->expectedIssuer !== null) {
            $this->constraints[] = new IssuedBy($this->expectedIssuer);
        }
    }

    public function validate(string $jwtToken): BearerTokenClaims
    {
        try {
            $token = $this->configuration->parser()->parse($jwtToken);
        } catch (Throwable $e) {
            throw new BearerTokenAuthenticationException('Access token format is invalid.', 0, $e);
        }

        if (!$token instanceof Plain) {
            throw new BearerTokenAuthenticationException('Access token format is invalid.');
        }

        if (!$this->configuration->validator()->validate($token, ...$this->constraints)) {
            throw new BearerTokenAuthenticationException('Access token signature or claims are invalid.');
        }

        $subject = $token->claims()->get(RegisteredClaims::SUBJECT, null);
        $tokenId = $token->claims()->get(RegisteredClaims::ID, null);

        if (!is_string($subject) || $subject === '') {
            throw new BearerTokenAuthenticationException('Access token does not contain valid "sub" claim.');
        }

        if (!is_string($tokenId) || $tokenId === '') {
            throw new BearerTokenAuthenticationException('Access token does not contain valid "jti" claim.');
        }

        return new BearerTokenClaims(
            $subject,
            $this->expectedAudience,
            $tokenId,
            $this->extractScopes($token->claims()->get('scopes', []))
        );
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
