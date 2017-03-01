<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 22.02.17
 * Time: 18:45
 */

use rollun\datastore\DataStore\Factory\CacheableAbstractFactory;

return [
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
        ['id' => "0", 'name' => 'guest'],
    ],

    'aclUserRoles' => [
        ['id' => 1, 'role_id' => 1, 'user_id' => '0'],
    ],

    'aclRoles' => [
        ['id' => 1, 'name' => 'guest', 'parent_id' => null],

    ],

    'aclResource' => [
        ['id' => 1, 'name' => 'root', 'pattern' => '/^http:\/\/' . constant("HOST") . '\//', 'parent_id' => null],
    ],

    'aclPrivilege' => [
        ['id' => 1, 'name' => 'GET'],
        ['id' => 2, 'name' => 'PUT'],
        ['id' => 3, 'name' => 'POST'],
        ['id' => 4, 'name' => 'DELETE'],
    ],

    'aclRules' => [
        ['id' => 1, 'role_id' => 1, 'resource_id' => 1, 'privilege_id' => 1, 'allow_flag' => 1],
        ['id' => 2, 'role_id' => 1, 'resource_id' => 1, 'privilege_id' => 2, 'allow_flag' => 1],
        ['id' => 3, 'role_id' => 1, 'resource_id' => 1, 'privilege_id' => 3, 'allow_flag' => 1],
        ['id' => 4, 'role_id' => 1, 'resource_id' => 1, 'privilege_id' => 4, 'allow_flag' => 1],
    ],
];