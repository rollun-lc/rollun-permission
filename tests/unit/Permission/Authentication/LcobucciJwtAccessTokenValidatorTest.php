<?php

declare(strict_types=1);

namespace rollun\test\unit\Permission\Authentication;

use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token\Plain;
use PHPUnit\Framework\TestCase;
use rollun\permission\Authentication\BearerTokenAuthenticationException;
use rollun\permission\Authentication\LcobucciJwtAccessTokenValidator;

class LcobucciJwtAccessTokenValidatorTest extends TestCase
{
    private const EXPECTED_AUDIENCE = 'crm-client';
    private const EXPECTED_ISSUER = 'oauth2-php';

    private string $publicKeyPath;
    private string $privateKeyPem;
    private string $privateKeyPemAlt;

    protected function setUp(): void
    {
        [$privatePem, $publicPem] = $this->generateRsaKeyPair();
        [$privatePemAlt, ] = $this->generateRsaKeyPair();

        $this->privateKeyPem = $privatePem;
        $this->privateKeyPemAlt = $privatePemAlt;

        $this->publicKeyPath = tempnam(sys_get_temp_dir(), 'pubkey-');
        if ($this->publicKeyPath === false) {
            $this->fail('Failed to create temp file for public key.');
        }

        file_put_contents($this->publicKeyPath, $publicPem);
    }

    protected function tearDown(): void
    {
        if (is_file($this->publicKeyPath)) {
            @unlink($this->publicKeyPath);
        }
    }

    public function testValidateReturnsClaimsForValidToken(): void
    {
        $token = $this->makeToken([
            'sub'    => 'user-1',
            'jti'    => 'tok-1',
            'aud'    => self::EXPECTED_AUDIENCE,
            'iat'    => '-60 seconds',
            'nbf'    => '-60 seconds',
            'exp'    => '+5 minutes',
            'scopes' => 'crm.read crm.write',
        ]);

        $validator = new LcobucciJwtAccessTokenValidator(
            $this->publicKeyPath,
            self::EXPECTED_AUDIENCE
        );

        $claims = $validator->validate($token);

        $this->assertSame('user-1', $claims->getSubject());
        $this->assertSame('tok-1', $claims->getTokenId());
        $this->assertSame(self::EXPECTED_AUDIENCE, $claims->getAudience());
        $this->assertSame(['crm.read', 'crm.write'], $claims->getScopes());
    }

    public function testValidateAcceptsMultipleAudiencesIfExpectedIsIncluded(): void
    {
        $token = $this->makeToken([
            'sub' => 'user-1',
            'jti' => 'tok-1',
            'aud' => ['other-service', self::EXPECTED_AUDIENCE, 'yet-another'],
            'iat' => '-60 seconds',
            'nbf' => '-60 seconds',
            'exp' => '+5 minutes',
        ]);

        $validator = new LcobucciJwtAccessTokenValidator(
            $this->publicKeyPath,
            self::EXPECTED_AUDIENCE
        );

        $claims = $validator->validate($token);

        $this->assertSame(self::EXPECTED_AUDIENCE, $claims->getAudience());
    }

    public function testValidateRejectsTokenWithWrongAudience(): void
    {
        $token = $this->makeToken([
            'sub' => 'user-1',
            'jti' => 'tok-1',
            'aud' => 'another-client',
            'iat' => '-60 seconds',
            'nbf' => '-60 seconds',
            'exp' => '+5 minutes',
        ]);

        $validator = new LcobucciJwtAccessTokenValidator(
            $this->publicKeyPath,
            self::EXPECTED_AUDIENCE
        );

        $this->expectException(BearerTokenAuthenticationException::class);
        $validator->validate($token);
    }

    public function testValidateRejectsTokenWithWrongIssuer(): void
    {
        $token = $this->makeToken([
            'sub' => 'user-1',
            'jti' => 'tok-1',
            'aud' => self::EXPECTED_AUDIENCE,
            'iss' => 'someone-else',
            'iat' => '-60 seconds',
            'nbf' => '-60 seconds',
            'exp' => '+5 minutes',
        ]);

        $validator = new LcobucciJwtAccessTokenValidator(
            $this->publicKeyPath,
            self::EXPECTED_AUDIENCE,
            self::EXPECTED_ISSUER
        );

        $this->expectException(BearerTokenAuthenticationException::class);
        $validator->validate($token);
    }

    public function testValidateIgnoresIssuerWhenNotExpected(): void
    {
        $token = $this->makeToken([
            'sub' => 'user-1',
            'jti' => 'tok-1',
            'aud' => self::EXPECTED_AUDIENCE,
            'iss' => 'whatever-issuer',
            'iat' => '-60 seconds',
            'nbf' => '-60 seconds',
            'exp' => '+5 minutes',
        ]);

        $validator = new LcobucciJwtAccessTokenValidator(
            $this->publicKeyPath,
            self::EXPECTED_AUDIENCE
        );

        $claims = $validator->validate($token);
        $this->assertSame('user-1', $claims->getSubject());
    }

    public function testValidateRejectsTokenSignedByDifferentKey(): void
    {
        $token = $this->makeToken(
            [
                'sub' => 'user-1',
                'jti' => 'tok-1',
                'aud' => self::EXPECTED_AUDIENCE,
                'iat' => '-60 seconds',
                'nbf' => '-60 seconds',
                'exp' => '+5 minutes',
            ],
            $this->privateKeyPemAlt
        );

        $validator = new LcobucciJwtAccessTokenValidator(
            $this->publicKeyPath,
            self::EXPECTED_AUDIENCE
        );

        $this->expectException(BearerTokenAuthenticationException::class);
        $validator->validate($token);
    }

    public function testValidateRejectsExpiredToken(): void
    {
        $token = $this->makeToken([
            'sub' => 'user-1',
            'jti' => 'tok-1',
            'aud' => self::EXPECTED_AUDIENCE,
            'iat' => '-1 hour',
            'nbf' => '-1 hour',
            'exp' => '-30 minutes',
        ]);

        $validator = new LcobucciJwtAccessTokenValidator(
            $this->publicKeyPath,
            self::EXPECTED_AUDIENCE
        );

        $this->expectException(BearerTokenAuthenticationException::class);
        $validator->validate($token);
    }

    public function testValidateRejectsTokenNotYetValid(): void
    {
        $token = $this->makeToken([
            'sub' => 'user-1',
            'jti' => 'tok-1',
            'aud' => self::EXPECTED_AUDIENCE,
            'iat' => '-10 seconds',
            'nbf' => '+30 minutes',
            'exp' => '+1 hour',
        ]);

        $validator = new LcobucciJwtAccessTokenValidator(
            $this->publicKeyPath,
            self::EXPECTED_AUDIENCE
        );

        $this->expectException(BearerTokenAuthenticationException::class);
        $validator->validate($token);
    }

    public function testValidateRejectsTokenWithoutSubClaim(): void
    {
        $token = $this->makeToken([
            'jti' => 'tok-1',
            'aud' => self::EXPECTED_AUDIENCE,
            'iat' => '-60 seconds',
            'nbf' => '-60 seconds',
            'exp' => '+5 minutes',
        ]);

        $validator = new LcobucciJwtAccessTokenValidator(
            $this->publicKeyPath,
            self::EXPECTED_AUDIENCE
        );

        $this->expectException(BearerTokenAuthenticationException::class);
        $this->expectExceptionMessageMatches('/sub/');
        $validator->validate($token);
    }

    public function testValidateRejectsTokenWithoutJtiClaim(): void
    {
        $token = $this->makeToken([
            'sub' => 'user-1',
            'aud' => self::EXPECTED_AUDIENCE,
            'iat' => '-60 seconds',
            'nbf' => '-60 seconds',
            'exp' => '+5 minutes',
        ]);

        $validator = new LcobucciJwtAccessTokenValidator(
            $this->publicKeyPath,
            self::EXPECTED_AUDIENCE
        );

        $this->expectException(BearerTokenAuthenticationException::class);
        $this->expectExceptionMessageMatches('/jti/');
        $validator->validate($token);
    }

    public function testValidateRejectsTokenWithoutAudClaim(): void
    {
        $token = $this->makeToken([
            'sub' => 'user-1',
            'jti' => 'tok-1',
            'iat' => '-60 seconds',
            'nbf' => '-60 seconds',
            'exp' => '+5 minutes',
        ]);

        $validator = new LcobucciJwtAccessTokenValidator(
            $this->publicKeyPath,
            self::EXPECTED_AUDIENCE
        );

        $this->expectException(BearerTokenAuthenticationException::class);
        $validator->validate($token);
    }

    public function testValidateRejectsMalformedJwtString(): void
    {
        $validator = new LcobucciJwtAccessTokenValidator(
            $this->publicKeyPath,
            self::EXPECTED_AUDIENCE
        );

        $this->expectException(BearerTokenAuthenticationException::class);
        $validator->validate('not-a-jwt.at.all');
    }

    public function testConstructorThrowsWhenAudienceIsEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new LcobucciJwtAccessTokenValidator($this->publicKeyPath, '');
    }

    public function testConstructorThrowsWhenKeyFileNotReadable(): void
    {
        $this->expectException(BearerTokenAuthenticationException::class);
        new LcobucciJwtAccessTokenValidator(
            '/tmp/definitely-not-there-' . uniqid('', true),
            self::EXPECTED_AUDIENCE
        );
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function generateRsaKeyPair(): array
    {
        $resource = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        if ($resource === false) {
            $this->fail('openssl_pkey_new failed: ' . openssl_error_string());
        }

        openssl_pkey_export($resource, $privatePem);
        $details = openssl_pkey_get_details($resource);
        $publicPem = $details['key'];

        return [$privatePem, $publicPem];
    }

    /**
     * @param array<string, mixed> $claimOverrides
     */
    private function makeToken(array $claimOverrides, ?string $signingKeyPem = null): string
    {
        $signingKeyPem = $signingKeyPem ?? $this->privateKeyPem;

        $configuration = Configuration::forAsymmetricSigner(
            new Sha256(),
            InMemory::plainText($signingKeyPem),
            InMemory::plainText($signingKeyPem)
        );

        $builder = $configuration->builder();

        $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));

        foreach ($claimOverrides as $name => $value) {
            switch ($name) {
                case 'sub':
                    $builder = $builder->relatedTo((string)$value);
                    break;
                case 'jti':
                    $builder = $builder->identifiedBy((string)$value);
                    break;
                case 'iss':
                    $builder = $builder->issuedBy((string)$value);
                    break;
                case 'aud':
                    if (is_array($value)) {
                        $builder = $builder->permittedFor(...array_map('strval', $value));
                    } else {
                        $builder = $builder->permittedFor((string)$value);
                    }
                    break;
                case 'iat':
                    $builder = $builder->issuedAt($now->modify((string)$value));
                    break;
                case 'nbf':
                    $builder = $builder->canOnlyBeUsedAfter($now->modify((string)$value));
                    break;
                case 'exp':
                    $builder = $builder->expiresAt($now->modify((string)$value));
                    break;
                case 'scopes':
                    $builder = $builder->withClaim('scopes', $value);
                    break;
                default:
                    $builder = $builder->withClaim((string)$name, $value);
            }
        }

        /** @var Plain $token */
        $token = $builder->getToken($configuration->signer(), $configuration->signingKey());

        return $token->toString();
    }
}
