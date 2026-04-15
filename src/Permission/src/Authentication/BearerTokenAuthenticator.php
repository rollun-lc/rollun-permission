<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace rollun\permission\Authentication;

use rollun\permission\DataStore\AclUsersTable;
use rollun\permission\UserProvider\UserProviderChain;

class BearerTokenAuthenticator
{
    private JwtAccessTokenValidatorInterface $jwtAccessTokenValidator;
    private TokenStatusCheckerInterface $tokenStatusChecker;
    private UserProviderChain $userProviderChain;
    private UserRolesResolver $userRolesResolver;

    public function __construct(
        JwtAccessTokenValidatorInterface $jwtAccessTokenValidator,
        TokenStatusCheckerInterface $tokenStatusChecker,
        UserProviderChain $userProviderChain,
        UserRolesResolver $userRolesResolver
    ) {
        $this->jwtAccessTokenValidator = $jwtAccessTokenValidator;
        $this->tokenStatusChecker = $tokenStatusChecker;
        $this->userProviderChain = $userProviderChain;
        $this->userRolesResolver = $userRolesResolver;
    }

    /**
     * @throws BearerTokenAuthenticationException
     */
    public function authenticate(string $jwtToken): BearerTokenAuthenticatedIdentity
    {
        $claims = $this->jwtAccessTokenValidator->validate($jwtToken);
        $this->tokenStatusChecker->checkAccessToken($claims);

        $user = $this->userProviderChain->getUser($claims->getSubject());
        if (!isset($user)) {
            throw new BearerTokenAuthenticationException('User from access token was not found.');
        }

        $roles = $this->userRolesResolver->getRolesByUserId($claims->getSubject());
        $name = isset($user[AclUsersTable::FILED_NAME]) ? (string)$user[AclUsersTable::FILED_NAME] : $claims->getSubject();

        return new BearerTokenAuthenticatedIdentity(
            $claims->getSubject(),
            $roles,
            $name,
            $claims->getAudience(),
            $claims->getTokenId(),
            $claims->getScopes()
        );
    }
}
