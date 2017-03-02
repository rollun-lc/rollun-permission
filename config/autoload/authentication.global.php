<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 01.03.17
 * Time: 14:12
 */

use rollun\actionrender\Factory\ActionRenderAbstractFactory;
use rollun\actionrender\Factory\LazyLoadDirectAbstractFactory;
use rollun\actionrender\Factory\MiddlewarePipeAbstractFactory;
use rollun\permission\Acl\Factory\AclFromDataStoreFactory;
use rollun\permission\Auth\Adapter\Factory\AuthAdapterAbstractFactory;
use rollun\permission\Auth\Middleware\Factory\IdentityFactory;
use rollun\permission\Auth\Middleware\Factory\UserResolverFactory;

return [

    'dependencies' => [
        'invokables' => [
            \rollun\permission\Auth\Middleware\LoginAction::class => \rollun\permission\Auth\Middleware\LoginAction::class,
            \rollun\actionrender\ReturnMiddleware::class => \rollun\actionrender\ReturnMiddleware::class,
            \rollun\permission\Api\Example\HelloUserAction::class =>
                \rollun\permission\Api\Example\HelloUserAction::class,
        ],
        'factories' => [
            \Zend\Session\SessionManager::class => \Zend\Session\Service\SessionManagerFactory::class,
            \rollun\permission\Auth\Middleware\IdentityAction::class =>
                \rollun\permission\Auth\Middleware\Factory\IdentityFactory::class,
            \rollun\permission\Auth\Middleware\LogoutAction::class =>
                \rollun\permission\Auth\Middleware\Factory\LogoutActionFactory::class,
            \rollun\permission\Auth\Middleware\UserResolver::class =>
                \rollun\permission\Auth\Middleware\Factory\UserResolverFactory::class
        ],
        'abstract_factories' => [
            \rollun\permission\Auth\Adapter\Factory\AuthAdapterAbstractFactory::class,
            \rollun\actionrender\Factory\LazyLoadDirectAbstractFactory::class,
            \rollun\actionrender\Factory\ActionRenderAbstractFactory::class,
            \rollun\actionrender\Factory\MiddlewarePipeAbstractFactory::class,
        ]
    ],

    AuthAdapterAbstractFactory::KEY_ADAPTER => [
        'GoogleOpenID' => [
            AuthAdapterAbstractFactory::KEY_ADAPTER_CONFIG => [
                'redirect_uri' => "http://" . constant("HOST") . "/login/GoogleOpenID/"
            ],
            AuthAdapterAbstractFactory::KEY_CLASS => \rollun\permission\Auth\Adapter\GoogleOpenID::class
        ],
        'BaseAuthIdentity' => [
            AuthAdapterAbstractFactory::KEY_CLASS => \rollun\permission\Auth\Adapter\BaseAuth::class
        ],
        'SessionIdentity' => [
            AuthAdapterAbstractFactory::KEY_CLASS => \rollun\permission\Auth\Adapter\Session::class
        ]
    ],

    LazyLoadDirectAbstractFactory::KEY_LAZY_LOAD => [
        'authenticateLLD' => [
            LazyLoadDirectAbstractFactory::KEY_DIRECT_FACTORY =>
                \rollun\permission\Auth\Middleware\Factory\AuthenticateDirectFactory::class
        ],
        'authenticatePrepareLLD' => [
            LazyLoadDirectAbstractFactory::KEY_DIRECT_FACTORY =>
                \rollun\permission\Auth\Middleware\Factory\AuthenticatePrepareDirectFactory::class
        ]
    ],

    IdentityFactory::KEY_IDENTITY => [
        IdentityFactory::KEY_ADAPTERS_SERVICE => [
            'BaseAuthIdentity',
            'SessionIdentity'
        ],
    ],

    UserResolverFactory::KEY_USER_RESOLVER => [
        UserResolverFactory::KEY_USER_DS_SERVICE => UserResolverFactory::DEFAULT_USER_DS,
        UserResolverFactory::KEY_ROLES_DS_SERVICE => AclFromDataStoreFactory::DEFAULT_ROLES_DS,
        UserResolverFactory::KEY_USER_ROLES_DS_SERVICE => UserResolverFactory::DEFAULT_USER_ROLES_DS,
    ],

    MiddlewarePipeAbstractFactory::KEY_AMP => [
        'loginServicePipe' => [
            MiddlewarePipeAbstractFactory::KEY_MIDDLEWARES => [
                'authenticateLLD'
            ]
        ],
        'loginPrepareServicePipe' => [
            MiddlewarePipeAbstractFactory::KEY_MIDDLEWARES => [
                'authenticatePrepareLLD',
                \rollun\actionrender\ReturnMiddleware::class
            ]
        ],
        'identifyPipe' => [
            MiddlewarePipeAbstractFactory::KEY_MIDDLEWARES => [
                \rollun\permission\Auth\Middleware\IdentityAction::class,
                \rollun\permission\Auth\Middleware\UserResolver::class
            ]
        ]
    ],

    ActionRenderAbstractFactory::KEY_AR => [
        'loginPageAR' => [
            ActionRenderAbstractFactory::KEY_AR_MIDDLEWARE => [
                ActionRenderAbstractFactory::KEY_ACTION_MIDDLEWARE_SERVICE =>
                    \rollun\permission\Auth\Middleware\LoginAction::class,
                ActionRenderAbstractFactory::KEY_RENDER_MIDDLEWARE_SERVICE => 'simpleHtmlJsonRenderer',
            ]
        ],
        'logoutAR' => [
            ActionRenderAbstractFactory::KEY_AR_MIDDLEWARE => [
                ActionRenderAbstractFactory::KEY_ACTION_MIDDLEWARE_SERVICE =>
                    \rollun\permission\Auth\Middleware\LogoutAction::class,
                ActionRenderAbstractFactory::KEY_RENDER_MIDDLEWARE_SERVICE => 'simpleHtmlJsonRenderer',
            ]
        ],
        'loginServiceAR' => [
            ActionRenderAbstractFactory::KEY_AR_MIDDLEWARE => [
                ActionRenderAbstractFactory::KEY_ACTION_MIDDLEWARE_SERVICE => 'loginServicePipe',
                ActionRenderAbstractFactory::KEY_RENDER_MIDDLEWARE_SERVICE => 'simpleHtmlJsonRenderer',
            ]
        ],
        'loginPrepareServiceAR' => [
            ActionRenderAbstractFactory::KEY_AR_MIDDLEWARE => [
                ActionRenderAbstractFactory::KEY_ACTION_MIDDLEWARE_SERVICE => 'loginPrepareServicePipe',
                ActionRenderAbstractFactory::KEY_RENDER_MIDDLEWARE_SERVICE => 'simpleHtmlJsonRenderer',
            ]
        ],
        'user-page' => [
            ActionRenderAbstractFactory::KEY_AR_MIDDLEWARE => [
                ActionRenderAbstractFactory::KEY_ACTION_MIDDLEWARE_SERVICE =>
                    \rollun\permission\Api\Example\HelloUserAction::class,
                ActionRenderAbstractFactory::KEY_RENDER_MIDDLEWARE_SERVICE => 'simpleHtmlJsonRenderer'
            ]
        ],
    ],
];
