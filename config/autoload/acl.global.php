<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 30.01.17
 * Time: 17:26
 */

use rollun\actionrender\Factory\MiddlewarePipeAbstractFactory;
use rollun\permission\Acl\Factory\AclFromDataStoreFactory;
use rollun\permission\Auth\Adapter\Factory\HttpAdapterAbstractFactory;
use rollun\permission\Auth\Adapter\Factory\OpenIDAdapterAbstractFactory;
use rollun\permission\Auth\Adapter\Resolver\Factory\OpenIDResolverAbstractFactory;
use rollun\permission\Auth\Adapter\Resolver\Factory\UserDSResolverAbstractFactory;
use rollun\permission\Auth\Middleware\Factory\AuthenticationAbstractFactory;
use rollun\permission\DataStore\Factory\MemoryDSFromConfigFactory;
use rollun\permission\Middleware\Factory\LazyLoadSwitchAbstractFactory;
use Zend\Permissions\Acl\Acl;

return [
    'dataStore' => [
        'rulesDS' => [
            MemoryDSFromConfigFactory::KEY_CONFIG => 'aclRules',
            MemoryDSFromConfigFactory::KEY_CLASS => \rollun\permission\DataStore\MemoryConfig::class,
        ],
        'rolesDS' => [
            MemoryDSFromConfigFactory::KEY_CONFIG => 'aclRoles',
            MemoryDSFromConfigFactory::KEY_CLASS => \rollun\permission\DataStore\MemoryConfig::class,
        ],
        'resourceDS' => [
            MemoryDSFromConfigFactory::KEY_CONFIG => 'aclResource',
            MemoryDSFromConfigFactory::KEY_CLASS => \rollun\permission\DataStore\MemoryConfig::class,
        ],
        'privilegeDS' => [
            MemoryDSFromConfigFactory::KEY_CONFIG => 'aclPrivilege',
            MemoryDSFromConfigFactory::KEY_CLASS => \rollun\permission\DataStore\MemoryConfig::class,
        ],
        'userDS' => [
            MemoryDSFromConfigFactory::KEY_CONFIG => 'aclUser',
            MemoryDSFromConfigFactory::KEY_CLASS => \rollun\permission\DataStore\MemoryConfig::class,
        ],
    ],

    'acl' => [
        AclFromDataStoreFactory::KEY_DS_RULE_SERVICE => 'rulesDS',
        AclFromDataStoreFactory::KEY_DS_ROLE_SERVICE => 'rolesDS',
        AclFromDataStoreFactory::KEY_DS_RESOURCE_SERVICE => 'resourceDS',
        AclFromDataStoreFactory::KEY_DS_PRIVILEGE_SERVICE => 'privilegeDS',
    ],

    'aclUser' => [
        ['id' => "108787658858627228573", 'name' => 'victor'],
        ['id' => "1", 'name' => 'user', 'password' => '123wqe321'],
        ['id' => "0", 'name' => 'guest', 'password' => ''],
    ],

    'aclUserRoles' => [
        ['id' => 0, 'role_id' => 2, 'user_id' => '108787658858627228573'],
        ['id' => 1, 'role_id' => 3, 'user_id' => '1'],
        ['id' => 3, 'role_id' => 1, 'user_id' => '0'],
    ],

    'aclRules' => [
        ['id' => 1, 'role_id' => 2, 'resource_id' => 1, 'privilege_id' => 1, 'allow_flag' => 1],
        ['id' => 2, 'role_id' => 2, 'resource_id' => 1, 'privilege_id' => 2, 'allow_flag' => 1],
        ['id' => 3, 'role_id' => 2, 'resource_id' => 1, 'privilege_id' => 3, 'allow_flag' => 1],
        ['id' => 4, 'role_id' => 2, 'resource_id' => 1, 'privilege_id' => 4, 'allow_flag' => 1],

        ['id' => 5, 'role_id' => 1, 'resource_id' => 2, 'privilege_id' => 1, 'allow_flag' => 1],
        ['id' => 6, 'role_id' => 1, 'resource_id' => 2, 'privilege_id' => 2, 'allow_flag' => 1],
        ['id' => 7, 'role_id' => 1, 'resource_id' => 2, 'privilege_id' => 3, 'allow_flag' => 1],
        ['id' => 8, 'role_id' => 1, 'resource_id' => 2, 'privilege_id' => 4, 'allow_flag' => 1],

        ['id' => 9, 'role_id' => 2, 'resource_id' => 3, 'privilege_id' => 1, 'allow_flag' => 1],
        ['id' => 10, 'role_id' => 2, 'resource_id' => 3, 'privilege_id' => 2, 'allow_flag' => 1],
        ['id' => 11, 'role_id' => 2, 'resource_id' => 3, 'privilege_id' => 3, 'allow_flag' => 1],
        ['id' => 12, 'role_id' => 2, 'resource_id' => 3, 'privilege_id' => 4, 'allow_flag' => 1],

    ],

    'aclRoles' => [
        ['id' => 1, 'name' => 'guest', 'parent_id' => null],
        ['id' => 2, 'name' => 'user', 'parent_id' => 1],
        ['id' => 3, 'name' => 'admin', 'parent_id' => 2],
    ],

    'aclResource' => [
        ['id' => 1, 'name' => 'root', 'pattern' => '/^http:\/\/' . constant("HOST") . '\/$/', 'parent_id' => null],
        ['id' => 2, 'name' => 'login', 'pattern' => '/^http:\/\/' . constant("HOST") . '\/login/', 'parent_id' => null],
        ['id' => 3, 'name' => 'logout', 'pattern' => '/^http:\/\/' . constant("HOST") . '\/logout$/', 'parent_id' => null],
        ['id' => 4, 'name' => 'interrupt', 'pattern' => '/^http:\/\/' . constant("HOST") . '\/interrupt/', 'parent_id' => null],
        ['id' => 5, 'name' => 'api-rest', 'pattern' => '/^http:\/\/' . constant("HOST") . '\/api\/rest/', 'parent_id' => null],
        ['id' => 6, 'name' => 'api', 'pattern' => '/^http:\/\/' . constant("HOST") . '\/api/', 'parent_id' => 5],
    ],

    'aclPrivilege' => [
        ['id' => 1, 'name' => 'GET'],
        ['id' => 2, 'name' => 'PUT'],
        ['id' => 3, 'name' => 'POST'],
        ['id' => 4, 'name' => 'DELETE'],
    ],

    'dependencies' => [
        'invokables' => [
            \rollun\permission\Auth\Middleware\ErrorHandler\CredentialErrorHandlerMiddleware::class =>
            \rollun\permission\Auth\Middleware\ErrorHandler\CredentialErrorHandlerMiddleware::class,
        ],

        'factories' => [
            \rollun\permission\Auth\Middleware\LogoutAction::class =>
                \rollun\permission\Auth\Middleware\Factory\LogoutActionFactory::class,

            \Zend\Authentication\AuthenticationService::class =>
                \rollun\permission\Auth\Factory\AuthServiceFactory::class,

            \Zend\Session\SessionManager::class => \Zend\Session\Service\SessionManagerFactory::class,

            \rollun\api\Api\Google\Client\Web::class => \rollun\api\Api\Google\Client\Factory\WebFactory::class,

            \rollun\permission\Auth\Middleware\AuthenticationAction::class => \rollun\permission\Auth\Middleware\Factory\AuthenticationAbstractFactory::class,
            \Zend\Authentication\Adapter\Http::class => \rollun\permission\Auth\Adapter\Factory\HttpAdapterAbstractFactory::class,
            \rollun\permission\Auth\Adapter\OpenIDAdapter::class => \rollun\permission\Auth\Adapter\Factory\OpenIDAdapterAbstractFactory::class,

        ],

        'abstract_factories' => [
            \rollun\permission\DataStore\Factory\MemoryDSFromConfigFactory::class,
            \rollun\permission\Auth\Adapter\Resolver\Factory\UserDSResolverAbstractFactory::class,
            \rollun\permission\Auth\Adapter\Resolver\Factory\OpenIDResolverAbstractFactory::class,
            \rollun\permission\Auth\Adapter\Factory\OpenIDAdapterAbstractFactory::class,
            \rollun\permission\Auth\Adapter\Factory\HttpAdapterAbstractFactory::class,
        ]
    ],

    AuthenticationAbstractFactory::KEY_AUTH => [
        AuthenticationAbstractFactory::KEY_ADAPTER => \rollun\permission\Auth\Adapter\OpenIDAdapter::class
    ],

    HttpAdapterAbstractFactory::KEY_ADAPTER => [
        'basicHttpAdapter' => [
            HttpAdapterAbstractFactory::KEY_BASIC_RESOLVER => 'httpBasicResolver',
        ]
    ],

    OpenIDAdapterAbstractFactory::KEY_ADAPTER => [
        'openIdAdapter' => [
            OpenIDAdapterAbstractFactory::KEY_RESOLVER => 'openIdResolver',
        ]
    ],

    UserDSResolverAbstractFactory::KEY_RESOLVER => [
        'httpBasicResolver' => [
            UserDSResolverAbstractFactory::KEY_DS_SERVICE => 'userDS',
        ],
    ],

    OpenIDResolverAbstractFactory::KEY_RESOLVER => [
        'openIdResolver' => [
            OpenIDResolverAbstractFactory::KEY_USER_DS_SERVICE => 'userDS',
        ]
    ],

    MiddlewarePipeAbstractFactory::KEY_AMP => [
        'authPipe' => [
            'middlewares' => [
                \rollun\permission\Auth\Middleware\IdentifyAction::class,
                'authPathSwitch',
            ]
        ]
    ],

    LazyLoadSwitchAbstractFactory::LAZY_LOAD_SWITCH => [
        'authPathSwitch' => [
            LazyLoadSwitchAbstractFactory::KEY_COMPARATOR_SERVICE => '',
            LazyLoadSwitchAbstractFactory::KEY_MIDDLEWARES_SERVICE => [
                
            ]
        ],
        'authTypeSwitch' => [
            LazyLoadSwitchAbstractFactory::KEY_COMPARATOR_SERVICE => '',
            LazyLoadSwitchAbstractFactory::KEY_MIDDLEWARES_SERVICE => [
                ''
            ]
        ]

    ],

    AuthenticationAbstractFactory::KEY_AUTHENTICATION_SERVICE => [
        'baseAuth' => [
            AuthenticationAbstractFactory::KEY_ADAPTER => 'basicHttpAdapter'
        ],
        'openId' => [
            AuthenticationAbstractFactory::KEY_ADAPTER => 'openIdAdapter'
        ],
    ]

];