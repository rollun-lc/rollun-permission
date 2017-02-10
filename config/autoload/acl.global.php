<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 30.01.17
 * Time: 17:26
 */

use rollun\permission\Acl\Factory\AclFromDataStoreFactory;
use rollun\permission\DataStore\Factory\MemoryDSFromConfigFactory;
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
        ['id' => "108787658858627228573", 'name' => 'victor', 'role' => 'user']
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

        ['id' => 9,  'role_id' => 2, 'resource_id' => 3, 'privilege_id' => 1, 'allow_flag' => 1],
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

        ],

        'factories' => [
            \rollun\permission\Auth\Middleware\LoginAction::class =>
                \rollun\permission\Auth\Middleware\Factory\LoginActionFactory::class,

            \rollun\permission\Auth\Middleware\LogoutAction::class =>
                \rollun\permission\Auth\Middleware\Factory\LogoutActionFactory::class,

            \Zend\Authentication\AuthenticationService::class =>
                \rollun\permission\Auth\Factory\AuthServiceFactory::class,

            \rollun\permission\Auth\Adapter\OpenIDAdapter::class =>
                \rollun\permission\Auth\Adapter\OpenIDAdapterFactory::class,

            \rollun\permission\Auth\OpenIDAuthManager::class =>
                \rollun\permission\Auth\Factory\OpenIDAuthManagerFactory::class,

            \rollun\permission\Auth\Middleware\AuthErrorHandlerMiddleware::class =>
                \rollun\permission\Auth\Middleware\Factory\AuthErrorHandlerFactory::class,

            \Zend\Session\SessionManager::class => \Zend\Session\Service\SessionManagerFactory::class,

            \rollun\api\Api\Google\Client\Web::class => \rollun\api\Api\Google\Client\Factory\WebFactory::class,
        ],

        'abstract_factories' => [
            \rollun\permission\DataStore\Factory\MemoryDSFromConfigFactory::class,
        ]
    ],
];