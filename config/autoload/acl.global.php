<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 30.01.17
 * Time: 17:26
 */

use rollun\permission\Acl\Factory\AclFromDataStoreFactory;
use rollun\permission\Api\Google\Client\Factory\OpenIDClientAbstractFactory;
use rollun\permission\DataStore\Factory\MemoryDSFromConfigFactory;

return [

    OpenIDClientAbstractFactory::GOOGLE_API_CLIENTS_SERVICES_KEY => [
        'OpenIDAuthClient' => [
            OpenIDClientAbstractFactory::SCOPES => [
                'openid'
            ]
        ]
    ],

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
        ['id' => 108787658858627228573, 'name' => 'victor', 'role' => 'user']
    ],

    'aclRules' => [
        ['id' => 1, 'role_id' => 2, 'resource_id' => 1, 'privilege_id' => 1],
        ['id' => 1, 'role_id' => 2, 'resource_id' => 1, 'privilege_id' => 2],
        ['id' => 1, 'role_id' => 2, 'resource_id' => 1, 'privilege_id' => 3],
        ['id' => 1, 'role_id' => 2, 'resource_id' => 1, 'privilege_id' => 4],

        ['id' => 1, 'role_id' => 1, 'resource_id' => 2, 'privilege_id' => 1],
        ['id' => 1, 'role_id' => 1, 'resource_id' => 2, 'privilege_id' => 2],
        ['id' => 1, 'role_id' => 1, 'resource_id' => 2, 'privilege_id' => 3],
        ['id' => 1, 'role_id' => 1, 'resource_id' => 2, 'privilege_id' => 4],

        ['id' => 1, 'role_id' => 2, 'resource_id' => 3, 'privilege_id' => 1],
        ['id' => 1, 'role_id' => 2, 'resource_id' => 3, 'privilege_id' => 2],
        ['id' => 1, 'role_id' => 2, 'resource_id' => 3, 'privilege_id' => 3],
        ['id' => 1, 'role_id' => 2, 'resource_id' => 3, 'privilege_id' => 4],

    ],

    'aclRoles' => [
        ['id' => 1, 'name' => 'guest', 'parent_id' => null],
        ['id' => 2, 'name' => 'user', 'parent_id' => 1],
        ['id' => 3, 'name' => 'admin', 'parent_id' => 2],
    ],

    'aclResource' => [
        ['id' => 1, 'name' => '', 'pattern' => '/http:\/\/' . constant("HOST") . '/', 'parent_id' => null],
        ['id' => 2, 'name' => '', 'pattern' => '/http:\/\/' . constant("HOST") . '\/login/', 'parent_id' => null],
        ['id' => 3, 'name' => '', 'pattern' => '/http:\/\/' . constant("HOST") . '\/logout/', 'parent_id' => null],
        ['id' => 4, 'name' => '', 'pattern' => '/http:\/\/' . constant("HOST") . '\/api/', 'parent_id' => null],
        ['id' => 5, 'name' => '', 'pattern' => '/http:\/\/' . constant("HOST") . '\/interrupt/', 'parent_id' => null],
        ['id' => 6, 'name' => '', 'pattern' => '/http:\/\/' . constant("HOST") . '\/api\/rest/', 'parent_id' => 2],
    ],

    'aclPrivilege' => [
        ['id' => 1, 'name' => 'GET'],
        ['id' => 2, 'name' => 'PUT'],
        ['id' => 3, 'name' => 'POST'],
        ['id' => 4, 'name' => 'DELETE'],
    ],

    'dependencies' => [
        'invokables' => [

        ],

        'factories' => [
            \rollun\permission\Auth\Middleware\LogoutAction::class =>
                \rollun\permission\Auth\Middleware\Factory\LoginActionFactory::class,

            \rollun\permission\Auth\Middleware\LogoutAction::class =>
                \rollun\permission\Auth\Middleware\Factory\LogoutActionFactory::class,

            \Zend\Authentication\AuthenticationService::class =>
                \rollun\permission\Auth\Factory\AuthServiceFactory::class,

            \rollun\permission\Auth\Adapter\OpenIDAdapter::class =>
                \rollun\permission\Auth\Adapter\OpenIDAdapterFactory::class,

            \rollun\permission\Auth\OpenIDAuthManager::class =>
                \rollun\permission\Auth\Factory\OpenIDAuthManagerFactory::class,

            \rollun\permission\Acl\Middleware\ResourceResolver::class =>
                \rollun\permission\Acl\Factory\ResourceResolverFactory::class,

            \rollun\permission\Auth\Middleware\Identification::class =>
                \rollun\permission\Auth\Middleware\Factory\IdentificationFactory::class,

            \rollun\permission\Auth\Middleware\AuthErrorHandlerMiddleware::class =>
                \rollun\permission\Auth\Middleware\Factory\AuthErrorHandlerFactory::class,

            \Zend\Session\SessionManager::class => \Zend\Session\Service\SessionManagerFactory::class,

            \rollun\permission\Acl\Middleware\AclMiddleware::class =>
                AclFromDataStoreFactory::class
        ],

        'abstract_factories' => [
            \rollun\permission\Api\Google\Client\Factory\OpenIDClientAbstractFactory::class,
            \rollun\permission\DataStore\Factory\MemoryDSFromConfigFactory::class,
        ]
    ],
];