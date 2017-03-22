<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 02.03.17
 * Time: 11:44
 */

use rollun\datastore\DataStore\Factory\CacheableAbstractFactory;

return [

    'dependencies' => [
        'abstract_factories' => [
            \rollun\permission\Acl\DataSource\Factory\ConfigDataSourceAbstractFactory::class,
        ]
    ],

    'dataSource' => [
        'aclRules' => [],
        'aclRoles' => [],
        'aclResource' => [],
        'aclPrivilege' => [],
        'aclUser' => [],
        'aclUserRoles' => [],
    ],
    'dataStore' => [
        'rulesDS' => [
            CacheableAbstractFactory::KEY_DATASOURCE => 'aclRules',
            CacheableAbstractFactory::KEY_CLASS => \rollun\datastore\DataStore\Cacheable::class,
            CacheableAbstractFactory::KEY_IS_REFRESH => true,
        ],
        'rolesDS' => [
            CacheableAbstractFactory::KEY_DATASOURCE => 'aclRoles',
            CacheableAbstractFactory::KEY_CLASS => \rollun\datastore\DataStore\Cacheable::class,
            CacheableAbstractFactory::KEY_IS_REFRESH => true,
        ],
        'resourceDS' => [
            CacheableAbstractFactory::KEY_DATASOURCE => 'aclResource',
            CacheableAbstractFactory::KEY_CLASS => \rollun\datastore\DataStore\Cacheable::class,
            CacheableAbstractFactory::KEY_IS_REFRESH => true,
        ],
        'privilegeDS' => [
            CacheableAbstractFactory::KEY_DATASOURCE => 'aclPrivilege',
            CacheableAbstractFactory::KEY_CLASS => \rollun\datastore\DataStore\Cacheable::class,
            CacheableAbstractFactory::KEY_IS_REFRESH => true,
        ],
        'userDS' => [
            CacheableAbstractFactory::KEY_DATASOURCE => 'aclUser',
            CacheableAbstractFactory::KEY_CLASS => \rollun\datastore\DataStore\Cacheable::class,
            CacheableAbstractFactory::KEY_IS_REFRESH => true,
        ],
        'userRolesDS' => [
            CacheableAbstractFactory::KEY_DATASOURCE => 'aclUserRoles',
            CacheableAbstractFactory::KEY_CLASS => \rollun\datastore\DataStore\Cacheable::class,
            CacheableAbstractFactory::KEY_IS_REFRESH => true,
        ],
    ],

    'aclUser' => [
        ['id' => "0", 'name' => 'guest', 'password' => ''],
        ['id' => "1", 'name' => 'service', 'password' => '123wqe321'],
        ['id' => "108787658858627228573", 'name' => 'victor'],
    ],

    'aclUserRoles' => [
        //guest -> guest
        ['id' => 0, 'role_id' => 1, 'user_id' => '0'],

        //service -> service
        ['id' => 1, 'role_id' => 3, 'user_id' => '1'],

        //victor -> user
        ['id' => 2, 'role_id' => 2, 'user_id' => '108787658858627228573'],
    ],

    'aclRoles' => [
        ['id' => 1, 'name' => 'guest', 'parent_id' => null],
        ['id' => 2, 'name' => 'user', 'parent_id' => 1],
        ['id' => 3, 'name' => 'service', 'parent_id' => 2],
    ],

    'aclResource' => [
        //all [GET]
        ['id' => 1, 'name' => 'root', 'pattern' => '/^http:\/\/' . constant("HOST") . '\/$/', 'parent_id' => null],

        //only user [GET]
        ['id' => 2, 'name' => 'user', 'pattern' => '/^http:\/\/' . constant("HOST") . '\/user$/', 'parent_id' => null],

        //all [GET]
        ['id' => 3, 'name' => 'login', 'pattern' => '/^http:\/\/' . constant("HOST") . '\/login/', 'parent_id' => null],
        ['id' => 4, 'name' => 'logout', 'pattern' => '/^http:\/\/' . constant("HOST") . '\/logout$/', 'parent_id' => null],

        //only service [GET POST]
        ['id' => 5, 'name' => 'webhook', 'pattern' => '/^http:\/\/' . constant("HOST") . '\/webhook/', 'parent_id' => null],

        //only service [GET POST PUT DELETE]
        ['id' => 6, 'name' => 'rest', 'pattern' => '/^http:\/\/' . constant("HOST") . '\/rest/', 'parent_id' => null],

        //only user  [GET POST PUT DELETE]
        ['id' => 7, 'name' => 'api', 'pattern' => '/^http:\/\/' . constant("HOST") . '\/api/', 'parent_id' => null],
    ],

    'aclPrivilege' => [
        ['id' => 1, 'name' => 'GET'],
        ['id' => 2, 'name' => 'PUT'],
        ['id' => 3, 'name' => 'POST'],
        ['id' => 4, 'name' => 'DELETE'],
    ],

    'aclRules' => [
        //all [GET] root
        ['id' => 1, 'role_id' => 1, 'resource_id' => 1, 'privilege_id' => 1, 'allow_flag' => 1],

        //only user [GET] user
        ['id' => 2, 'role_id' => 2, 'resource_id' => 2, 'privilege_id' => 1, 'allow_flag' => 1],

        //all [GET] login
        ['id' => 3, 'role_id' => 1, 'resource_id' => 3, 'privilege_id' => 1, 'allow_flag' => 1],

        //all [GET] logout
        ['id' => 4, 'role_id' => 1, 'resource_id' => 4, 'privilege_id' => 1, 'allow_flag' => 1],

        //only service [GET POST] webhook
        ['id' => 5, 'role_id' => 3, 'resource_id' => 5, 'privilege_id' => 1, 'allow_flag' => 1],
        ['id' => 6, 'role_id' => 3, 'resource_id' => 5, 'privilege_id' => 3, 'allow_flag' => 1],

        //only service [GET POST PUT DELETE] rest
        ['id' => 7, 'role_id' => 3, 'resource_id' => 6, 'privilege_id' => 1, 'allow_flag' => 1],
        ['id' => 8, 'role_id' => 3, 'resource_id' => 6, 'privilege_id' => 2, 'allow_flag' => 1],
        ['id' => 9, 'role_id' => 3, 'resource_id' => 6, 'privilege_id' => 3, 'allow_flag' => 1],
        ['id' => 10, 'role_id' => 3, 'resource_id' => 6, 'privilege_id' => 4, 'allow_flag' => 1],

        //only user  [GET POST PUT DELETE] api
        ['id' => 11, 'role_id' => 2, 'resource_id' => 7, 'privilege_id' => 1, 'allow_flag' => 1],
        ['id' => 12, 'role_id' => 2, 'resource_id' => 7, 'privilege_id' => 2, 'allow_flag' => 1],
        ['id' => 13, 'role_id' => 2, 'resource_id' => 7, 'privilege_id' => 3, 'allow_flag' => 1],
        ['id' => 14, 'role_id' => 2, 'resource_id' => 7, 'privilege_id' => 4, 'allow_flag' => 1],
    ],
];