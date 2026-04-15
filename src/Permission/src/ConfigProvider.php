<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\permission;

use Laminas\Permissions\Acl\Acl;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Authentication\AuthenticationMiddleware;
use Mezzio\Authentication\DefaultUser;
use Mezzio\Authentication\UserInterface;
use Mezzio\Authentication\UserRepositoryInterface;
use Mezzio\Helper\UrlHelper;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use rollun\datastore\DataStore\Factory\DataStoreAbstractFactory;
use rollun\datastore\DataStore\Factory\DbTableAbstractFactory;
use rollun\datastore\TableGateway\Factory\TableGatewayAbstractFactory;
use rollun\permission\Authentication\Factory\AuthenticationChainAbstractFactory;
use rollun\permission\Authentication\Factory\BearerTokenAuthenticatorFactory;
use rollun\permission\Authentication\Factory\DataStoreTokenStatusCheckerFactory;
use rollun\permission\Authentication\Factory\LcobucciJwtAccessTokenValidatorFactory;
use rollun\permission\Authentication\Factory\BasicAccessAbstractFactory;
use rollun\permission\Authentication\Factory\GuestAuthenticationFactory;
use rollun\permission\Authentication\Factory\PhpSessionAbstractFactory;
use rollun\permission\Authentication\Factory\UserRolesResolverFactory;
use rollun\permission\Authentication\Factory\UserRepositoryFactory;
use rollun\permission\Authentication\BearerTokenAuthenticator;
use rollun\permission\Authentication\DataStoreTokenStatusChecker;
use rollun\permission\Authentication\GuestAuthentication;
use rollun\permission\Authentication\JwtAccessTokenValidatorInterface;
use rollun\permission\Authentication\LcobucciJwtAccessTokenValidator;
use rollun\permission\Authentication\RequestAttributeAuthentication;
use rollun\permission\Authentication\TokenStatusCheckerInterface;
use rollun\permission\Authentication\UserRepository;
use rollun\permission\Authentication\UserRolesResolver;
use rollun\permission\Middleware\BearerTokenAuthenticationMiddleware;
use rollun\permission\Middleware\Factory\BearerTokenAuthenticationMiddlewareFactory;
use rollun\permission\Authorization\Factory\AclFromDataStoreFactory;
use rollun\permission\Authorization\Middleware\AccessForbiddenHandler;
use rollun\permission\Authorization\Middleware\AclMiddleware;
use rollun\permission\Authorization\Middleware\Factory\AclMiddlewareFactory;
use rollun\permission\Authorization\Middleware\Factory\ResourceResolverAbstractFactory;
use rollun\permission\Authorization\Middleware\Factory\RoleResolverFactory;
use rollun\permission\Authorization\Middleware\PrivilegeResolver;
use rollun\permission\Authorization\Middleware\ResourceResolver;
use rollun\permission\Authorization\Middleware\RoleResolver;
use rollun\permission\Authorization\ResourceProducer\AbstractResourceProducerAbstractFactory;
use rollun\permission\Authorization\ResourceProducer\RouteAttributeAbstractFactory;
use rollun\permission\Authorization\ResourceProducer\RouteNameAbstractFactory;
use rollun\permission\Authorization\ResourceProducer\RouteReceiver\ExpressiveRouteName;
use rollun\permission\DataStore\AclPrivilegeTable;
use rollun\permission\DataStore\AclResourceTable;
use rollun\permission\DataStore\AclRolesTable;
use rollun\permission\DataStore\AclRulesTable;
use rollun\permission\DataStore\AclUserRolesTable;
use rollun\permission\DataStore\AclUsersTable;
use rollun\permission\DataStore\OAuthAccessTokensTable;
use rollun\permission\DataStore\OAuthClientsTable;
use rollun\permission\OAuth\AbstractOAuthMiddlewareFactory;
use rollun\permission\OAuth\CredentialMiddlewareAbstractFactory;
use rollun\permission\OAuth\GoogleClient;
use rollun\permission\OAuth\GoogleClientFactory;
use rollun\permission\OAuth\LoginMiddleware;
use rollun\permission\OAuth\RedirectMiddleware;
use rollun\permission\OAuth\RedirectMiddlewareFactory;
use rollun\permission\OAuth\RegisterMiddleware;
use rollun\permission\UserProvider\GetUserById;
use rollun\permission\UserProvider\GetUserByName;
use rollun\permission\UserProvider\UserProviderChain;
use rollun\utils\Factory\AbstractServiceAbstractFactory;

/**
 * This config providers contain basic rollun-permission configuration
 * To inject default configs:
 * - install 'rollun\permission\Installers\AuthenticationInstaller' for authentication
 * - install 'rollun\permission\Installers\AuthorizationInstaller' for authorization
 *
 * @see https://docs.zendframework.com/zend-component-installer/
 */
class ConfigProvider
{
    const AUTHENTICATION_MIDDLEWARE_SERVICE = 'authenticationMiddleware';
    const AUTHORIZATION_MIDDLEWARE_SERVICE = 'authorizationMiddleware';

    const USER_DATASTORE_SERVICE = 'userDataStore';
    const ROLE_DATASTORE_SERVICE = 'roleDataStore';
    const USER_ROLE_DATASTORE_SERVICE = 'userRoleDataStore';
    const RULE_DATASTORE_SERVICE = 'ruleDataStore';
    const RESOURCE_DATASTORE_SERVICE = 'resourceDataStore';
    const PRIVILEGE_DATASTORE_SERVICE = 'privilegeDataStore';
    const OAUTH_ACCESS_TOKENS_DATASTORE_SERVICE = 'oauthAccessTokensDataStore';
    const OAUTH_CLIENTS_DATASTORE_SERVICE = 'oauthClientsDataStore';

    const OAUTH_LOGIN_ROUTE_NAME = 'google-oauth-login';
    const OAUTH_REGISTER_ROUTE_NAME = 'google-oauth-register';

    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            PermissionMiddlewareFactory::class => $this->getPermissionMiddlewareConfig(),
            TableGatewayAbstractFactory::KEY_TABLE_GATEWAY => $this->getTableGatewayConfig(),
            DataStoreAbstractFactory::KEY_DATASTORE => $this->getDataStoreConfig(),
            MiddlewarePipeAbstractFactory::class => $this->getMiddlewarePipeConfig(),
            ResourceResolverAbstractFactory::class => $this->getResourceResolverConfig(),
            AbstractResourceProducerAbstractFactory::KEY => $this->getResourceProducerConfig(),
            AclFromDataStoreFactory::class => $this->getAclConfig(),
            AuthenticationChainAbstractFactory::class => $this->getAuthenticationServiceChainConfig(),
            BasicAccessAbstractFactory::class => $this->getBasicAccessConfig(),
            PhpSessionAbstractFactory::class => $this->getPhpSessionConfig(),
            UserRepositoryFactory::class => $this->getUserRepositoryConfig(),
            GoogleClientFactory::class => $this->getGoogleClientConfig(),
            'oauth2' => $this->getOAuth2Config(),
            AbstractOAuthMiddlewareFactory::class => $this->getOAuthMiddlewareConfig(),
            AbstractServiceAbstractFactory::KEY => $this->getAbstractServiceAbstractFactoryConfig(),
        ];
    }

    private function getAbstractServiceAbstractFactoryConfig()
    {
        return [
            UserProviderChain::class => [
                AbstractServiceAbstractFactory::KEY_CLASS => UserProviderChain::class,
                AbstractServiceAbstractFactory::KEY_DEPENDENCIES => [
                    'userProviders' => [
                        AbstractServiceAbstractFactory::KEY_TYPE => AbstractServiceAbstractFactory::TYPE_SERVICES_LIST,
                        AbstractServiceAbstractFactory::KEY_VALUE => [
                            GetUserById::class,
                            GetUserByName::class,
                        ]
                    ]
                ]
            ],
            GetUserById::class => [
                AbstractServiceAbstractFactory::KEY_CLASS => GetUserById::class,
                AbstractServiceAbstractFactory::KEY_DEPENDENCIES => [
                    'users' => self::USER_DATASTORE_SERVICE
                ]
            ],
            GetUserByName::class => [
                AbstractServiceAbstractFactory::KEY_CLASS => GetUserByName::class,
                AbstractServiceAbstractFactory::KEY_DEPENDENCIES => [
                    'users' => self::USER_DATASTORE_SERVICE
                ]
            ]
        ];
    }

    protected function getOAuthMiddlewareConfig()
    {
        return [
            CredentialMiddlewareAbstractFactory::class => [
                LoginMiddleware::class => [
                    CredentialMiddlewareAbstractFactory::KEY_CLASS => LoginMiddleware::class,
                    CredentialMiddlewareAbstractFactory::KEY_USER_REPOSITORY => 'WithoutPassUserRepository',
                ],
                RegisterMiddleware::class => [
                    CredentialMiddlewareAbstractFactory::KEY_CLASS => RegisterMiddleware::class,
                    CredentialMiddlewareAbstractFactory::KEY_USER_REPOSITORY => 'WithoutPassUserRepository',
                ],
            ],
            AbstractOAuthMiddlewareFactory::KEY_OAUTH_CONFIG => [
                'scopes' => 'email profile',
                'host' => getenv('HOST'),
                'loginRouteName' => self::OAUTH_LOGIN_ROUTE_NAME,
                'registerRouteName' => self::OAUTH_REGISTER_ROUTE_NAME,
                'email' => [
                    'bodyTemplate' => "User with email \':email\' and id \':userId\' ask for registration.",
                    'from' => getenv('EMAIL_FROM'),
                    'subject' => 'New user registration',
                    'to' => getenv('EMAIL_TO'),
                    'smtpOptions' => [
                        'name' => 'aspmx.l.google.com',
                        'host' => 'aspmx.l.google.com',
                        'port' => 25,
                    ],
                ],
            ],
        ];
    }

    protected function getGoogleClientConfig()
    {
        return [
            'client_secret' => getenv('GOOGLE_CLIENT_SECRET'),
            'project_id' => getenv('GOOGLE_PROJECT_ID'),
            'client_id' => getenv('GOOGLE_CLIENT_ID'),
        ];
    }

    protected function getOAuth2Config(): array
    {
        return [
            'public_key_path' => '/data/oauth/public.key',
        ];
    }

    /**
     * Returns the container dependencies
     *
     * @return array
     */
    protected function getDependencies()
    {
        return [
            'factories' => [
                PermissionMiddleware::class => PermissionMiddlewareFactory::class,
                BearerTokenAuthenticationMiddleware::class => BearerTokenAuthenticationMiddlewareFactory::class,
                ExpressiveRouteName::class => InvokableFactory::class,
                ResourceResolver::class => ResourceResolverAbstractFactory::class,
                RoleResolver::class => RoleResolverFactory::class,
                AclMiddleware::class => AclMiddlewareFactory::class,
                Acl::class => AclFromDataStoreFactory::class,
                UserRepository::class => UserRepositoryFactory::class,
                UserRolesResolver::class => UserRolesResolverFactory::class,
                BearerTokenAuthenticator::class => BearerTokenAuthenticatorFactory::class,
                LcobucciJwtAccessTokenValidator::class => LcobucciJwtAccessTokenValidatorFactory::class,
                DataStoreTokenStatusChecker::class => DataStoreTokenStatusCheckerFactory::class,
                'WithoutPassUserRepository' => function (ContainerInterface $container) {
                    $userDataStore = $container->get(AssetInstaller::USER_DATASTORE_SERVICE);
                    $userRoleDataStore = $container->get(AssetInstaller::USER_ROLE_DATASTORE_SERVICE);
                    $roleDataStore = $container->get(AssetInstaller::ROLE_DATASTORE_SERVICE);
                    $config = [
                        'details' => [AclUsersTable::FILED_NAME],
                        'without_password' => true
                    ];
                    $userFactory = function (string $identity, array $roles = [], array $details = []): UserInterface {
                        return new DefaultUser($identity, $roles, $details);
                    };

                    return new UserRepository(
                        $userDataStore,
                        $userRoleDataStore,
                        $roleDataStore,
                        $userFactory,
                        $config,
                        $container->get(UserProviderChain::class),
                        $container->get(LoggerInterface::class),
                        $container->get(UserRolesResolver::class)
                    );
                },
                GuestAuthentication::class => GuestAuthenticationFactory::class,
                RedirectMiddleware::class => RedirectMiddlewareFactory::class,
                GoogleClient::class => GoogleClientFactory::class,
                AccessForbiddenHandler::class => function (ContainerInterface $container) {
                    $urlHelper = $container->get(UrlHelper::class);
                    return new AccessForbiddenHandler($urlHelper);
                }
            ],
            'aliases' => [
                ConfigProvider::AUTHENTICATION_MIDDLEWARE_SERVICE => AuthenticationMiddleware::class,
                AuthenticationInterface::class => 'authenticationServiceChain',
                UserRepositoryInterface::class => UserRepository::class,
                JwtAccessTokenValidatorInterface::class => LcobucciJwtAccessTokenValidator::class,
                TokenStatusCheckerInterface::class => DataStoreTokenStatusChecker::class,

                self::RULE_DATASTORE_SERVICE => AclRulesTable::class,
                self::ROLE_DATASTORE_SERVICE => AclRolesTable::class,
                self::RESOURCE_DATASTORE_SERVICE => AclResourceTable::class,
                self::PRIVILEGE_DATASTORE_SERVICE => AclPrivilegeTable::class,
                self::USER_DATASTORE_SERVICE => AclUsersTable::class,
                self::USER_ROLE_DATASTORE_SERVICE => AclUserRolesTable::class,
                self::OAUTH_ACCESS_TOKENS_DATASTORE_SERVICE => OAuthAccessTokensTable::class,
                self::OAUTH_CLIENTS_DATASTORE_SERVICE => OAuthClientsTable::class,
            ],
            'abstract_factories' => [
                ResourceResolverAbstractFactory::class,
                RouteAttributeAbstractFactory::class,
                RouteNameAbstractFactory::class,
                MiddlewarePipeAbstractFactory::class,
                AuthenticationChainAbstractFactory::class,
                BasicAccessAbstractFactory::class,
                PhpSessionAbstractFactory::class,
                CredentialMiddlewareAbstractFactory::class,
            ],
            'invokables' => [
                PrivilegeResolver::class => PrivilegeResolver::class,
                ExpressiveRouteName::class => ExpressiveRouteName::class,
                RequestAttributeAuthentication::class => RequestAttributeAuthentication::class,
            ],
        ];
    }

    protected function getTableGatewayConfig()
    {
        return [
            AclUsersTable::TABLE_NAME => [],
            AclRolesTable::TABLE_NAME => [],
            AclPrivilegeTable::TABLE_NAME => [],
            AclResourceTable::TABLE_NAME => [],
            AclUserRolesTable::TABLE_NAME => [],
            AclRulesTable::TABLE_NAME => [],
        ];
    }

    protected function getDataStoreConfig()
    {
        return [
            AclUsersTable::class => [
                "class" => AclUsersTable::class,
                DbTableAbstractFactory::KEY_TABLE_GATEWAY => AclUsersTable::TABLE_NAME,
            ],
            AclRolesTable::class => [
                "class" => AclRolesTable::class,
                DbTableAbstractFactory::KEY_TABLE_GATEWAY => AclRolesTable::TABLE_NAME,
            ],
            AclPrivilegeTable::class => [
                "class" => AclPrivilegeTable::class,
                DbTableAbstractFactory::KEY_TABLE_GATEWAY => AclPrivilegeTable::TABLE_NAME,
            ],
            AclResourceTable::class => [
                "class" => AclResourceTable::class,
                DbTableAbstractFactory::KEY_TABLE_GATEWAY => AclResourceTable::TABLE_NAME,
            ],
            AclUserRolesTable::class => [
                "class" => AclUserRolesTable::class,
                DbTableAbstractFactory::KEY_TABLE_GATEWAY => AclUserRolesTable::TABLE_NAME,
            ],
            AclRulesTable::class => [
                "class" => AclRulesTable::class,
                DbTableAbstractFactory::KEY_TABLE_GATEWAY => AclRulesTable::TABLE_NAME,
            ],
        ];
    }

    protected function getAclConfig()
    {
        return [
            AclFromDataStoreFactory::KEY_DATASTORE_PRIVILEGE_SERVICE => AssetInstaller::PRIVILEGE_DATASTORE_SERVICE,
            AclFromDataStoreFactory::KEY_DATASTORE_RESOURCE_SERVICE => AssetInstaller::RESOURCE_DATASTORE_SERVICE,
            AclFromDataStoreFactory::KEY_DATASTORE_ROLE_SERVICE => AssetInstaller::ROLE_DATASTORE_SERVICE,
            AclFromDataStoreFactory::KEY_DATASTORE_RULE_SERVICE => AssetInstaller::RULE_DATASTORE_SERVICE,
        ];
    }

    protected function getResourceResolverConfig()
    {
        return [
            ResourceResolver::class => [
                ResourceResolverAbstractFactory::KEY_RESOURCE_PRODUCERS => [
                    [
                        ResourceResolverAbstractFactory::KEY_SERVICE_NAME => 'resourceNameAttribute',
                        ResourceResolverAbstractFactory::KEY_PRIORITY => 10
                    ],
                    [
                        ResourceResolverAbstractFactory::KEY_SERVICE_NAME => 'routeName',
                        ResourceResolverAbstractFactory::KEY_PRIORITY => 20
                    ],
                ],
            ],
        ];
    }

    protected function getResourceProducerConfig()
    {
        return [
            RouteAttributeAbstractFactory::class => [
                'resourceNameAttribute' => [
                    RouteAttributeAbstractFactory::KEY_ATTRIBUTE_NAME => 'resourceName',
                    RouteAttributeAbstractFactory::KEY_ROUTE_NAME_RECEIVER => ExpressiveRouteName::class,
                ],
            ],
            RouteNameAbstractFactory::class => [
                'routeName' => [
                    RouteAttributeAbstractFactory::KEY_ROUTE_NAME_RECEIVER => ExpressiveRouteName::class,
                ],
            ],
        ];
    }

    protected function getMiddlewarePipeConfig()
    {
        return [
            ConfigProvider::AUTHORIZATION_MIDDLEWARE_SERVICE => [
                MiddlewarePipeAbstractFactory::KEY_MIDDLEWARES => [
                    RoleResolver::class,
                    ResourceResolver::class,
                    PrivilegeResolver::class,
                    AclMiddleware::class,
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    protected function getUserRepositoryConfig()
    {
        return [
            UserRepositoryFactory::KEY_USER_DATASTORE => AssetInstaller::USER_DATASTORE_SERVICE,
            UserRepositoryFactory::KEY_USER_ROLE_DATASTORE => AssetInstaller::USER_ROLE_DATASTORE_SERVICE,
            UserRepositoryFactory::KEY_ROLE_DATASTORE => AssetInstaller::ROLE_DATASTORE_SERVICE,
            UserRepositoryFactory::KEY_CONFIG => ['details' => [AclUsersTable::FILED_NAME]],
        ];
    }

    /**
     * @return array
     */
    protected function getBasicAccessConfig()
    {
        return [
            'basicAccess' => [
                BasicAccessAbstractFactory::KEY_REALM => 'realmValue',
                BasicAccessAbstractFactory::KEY_USER_REPOSITORY => UserRepository::class,
            ],
        ];
    }

    /**
     * @return array
     */
    protected function getPhpSessionConfig()
    {
        return [
            'phpSession' => [
                PhpSessionAbstractFactory::KEY_CONFIG => ['redirect' => '/login'],
                PhpSessionAbstractFactory::KEY_USER_REPOSITORY => 'WithoutPassUserRepository',
            ],
        ];
    }

    /**
     * @return array
     */
    protected function getAuthenticationServiceChainConfig()
    {
        return [
            'authenticationServiceChain' => [
                AuthenticationChainAbstractFactory::KEY_AUTHENTICATION_SERVICES => [
                    RequestAttributeAuthentication::class,
                    'basicAccess',
                    'phpSession',
                    GuestAuthentication::class,
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    protected function getPermissionMiddlewareConfig()
    {
        return [
            PermissionMiddlewareFactory::KEY_ACL_AUTHENTICATION => self::AUTHENTICATION_MIDDLEWARE_SERVICE,
            PermissionMiddlewareFactory::KEY_ACL_AUTHORIZATION => self::AUTHORIZATION_MIDDLEWARE_SERVICE,
        ];
    }
}
