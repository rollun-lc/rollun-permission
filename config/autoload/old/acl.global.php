<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 30.01.17
 * Time: 17:26
 */

use rollun\actionrender\Factory\MiddlewarePipeAbstractFactory;
use rollun\permission\Acl\Factory\AclFromDataStoreFactory;
use rollun\permission\Auth\Middleware\Factory\UserResolverFactory;

return [

    'acl' => [
        AclFromDataStoreFactory::KEY_DS_RULE_SERVICE => AclFromDataStoreFactory::DEFAULT_RULES_DS,
        AclFromDataStoreFactory::KEY_DS_ROLE_SERVICE => AclFromDataStoreFactory::DEFAULT_ROLES_DS,
        AclFromDataStoreFactory::KEY_DS_RESOURCE_SERVICE => AclFromDataStoreFactory::DEFAULT_RESOURCE_DS,
        AclFromDataStoreFactory::KEY_DS_PRIVILEGE_SERVICE => AclFromDataStoreFactory::DEFAULT_PRIVILEGE_DS,
    ],

    'dependencies' => [
        'invokables' => [
            \rollun\permission\Acl\Middleware\PrivilegeResolver::class =>
                \rollun\permission\Acl\Middleware\PrivilegeResolver::class
        ],
        'factories' => [
            \Zend\Session\SessionManager::class => \Zend\Session\Service\SessionManagerFactory::class,
            \Zend\Permissions\Acl\Acl::class => rollun\permission\Acl\Factory\AclFromDataStoreFactory::class,

            \rollun\permission\Acl\Middleware\ResourceResolver::class =>
                \rollun\permission\Acl\Middleware\Factory\ResourceResolverFactory::class,
            \rollun\permission\Acl\Middleware\RoleResolver::class =>
                \rollun\permission\Acl\Middleware\Factory\RoleResolverFactory::class,
            \rollun\permission\Acl\Middleware\AclMiddleware::class =>
                \rollun\permission\Acl\Middleware\Factory\AclMiddlewareFactory::class,
        ],
        'abstract_factories' => [
        ]
    ],

    MiddlewarePipeAbstractFactory::KEY => [
        'aclPipes' => [
            'middlewares' => [
                \rollun\permission\Acl\Middleware\RoleResolver::class,
                \rollun\permission\Acl\Middleware\ResourceResolver::class,
                \rollun\permission\Acl\Middleware\PrivilegeResolver::class,
                \rollun\permission\Acl\Middleware\AclMiddleware::class,
            ]
        ]
    ],


];