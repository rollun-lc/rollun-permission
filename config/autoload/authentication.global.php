<?php
/**
 * Created by PhpStorm.
 * User: victorsecuring
 * Date: 24.02.17
 * Time: 5:59 PM
 */

use rollun\actionrender\Factory\ActionRenderAbstractFactory;
use rollun\actionrender\Factory\LazyLoadSwitchAbstractFactory;
use rollun\actionrender\Factory\MiddlewarePipeAbstractFactory;
use rollun\permission\Auth\Adapter\Factory\AuthAdapterAbstractFactory;
use rollun\permission\Auth\Middleware\Factory\AllowAuthResolverAbstractFactory;
use rollun\permission\Auth\Middleware\Factory\AuthenticationAbstractFactory;
use rollun\permission\Comparator\Factory\AllowAuthAbstractFactory;

return [

    ActionRenderAbstractFactory::KEY_AR_SERVICE => [
        'login-service' => [
            ActionRenderAbstractFactory::KEY_AR_MIDDLEWARE => [
                ActionRenderAbstractFactory::KEY_ACTION_MIDDLEWARE_SERVICE =>
                    'loginPipe',
                ActionRenderAbstractFactory::KEY_RENDER_MIDDLEWARE_SERVICE => 'simpleHtmlJsonRenderer'
            ]
        ],
    ],

    MiddlewarePipeAbstractFactory::KEY_AMP => [
        'identityPipe' => [
            'middlewares' => [
                \rollun\permission\Auth\Middleware\IdentifyAction::class,
            ]
        ],
        'loginPipe' => [
            'middlewares' => [
                rollun\permission\Auth\Middleware\AllowAuthResolver::class,
                'lazyAuthAdapterSwitch',
            ]
        ],
        'quickAuthPipe' => [
            'middlewares' => [
                rollun\permission\Auth\Middleware\AllowAuthResolver::class,
                'quickAuthAdapterSwitch',
            ]
        ]
    ],

    LazyLoadSwitchAbstractFactory::LAZY_LOAD_SWITCH => [
        'lazyAuthAdapterSwitch' => [
            LazyLoadSwitchAbstractFactory::KEY_MIDDLEWARES_SERVICE => [
                'OpenID' =>  'lazyOpenID',
                'null' => 'lazyNull',
            ]
        ],
        'quickAuthAdapterSwitch' =>[
            LazyLoadSwitchAbstractFactory::KEY_MIDDLEWARES_SERVICE => [
                'BaseAuth' => 'quickBaseAuth',
            ]
        ]
    ],

    AuthenticationAbstractFactory::KEY_AUTHENTICATION => [
        'lazyNull' => [
            AuthenticationAbstractFactory::KEY_ADAPTER => \rollun\permission\Auth\Adapter\NullAdapter::class,
            AuthenticationAbstractFactory::KEY_CLASS => \rollun\permission\Auth\Middleware\LazyAuthenticationAction::class
        ],
        'lazyOpenID' => [
            AuthenticationAbstractFactory::KEY_ADAPTER => \rollun\permission\Auth\Adapter\OpenID::class,
            AuthenticationAbstractFactory::KEY_CLASS => \rollun\permission\Auth\Middleware\LazyAuthenticationAction::class
        ],
        'quickBaseAuth' => [
            AuthenticationAbstractFactory::KEY_ADAPTER => \rollun\permission\Auth\Adapter\BaseAuth::class,
            AuthenticationAbstractFactory::KEY_CLASS => \rollun\permission\Auth\Middleware\QuickAuthenticationAction::class
        ]
    ],

    AllowAuthAbstractFactory::KEY_ALLOW_AUTH => [
        \rollun\permission\Comparator\AllowAuth::class => [
            AllowAuthAbstractFactory::KEY_CONFIG => [
                '/\/api/' => [
                    'null',
                ],
                '/\/webhook/' => [
                    'BaseAuth',
                ],
                '/\/rest/' => [
                    'BaseAuth',
                ],
                '/\/login/' => [
                    'OpenID',
                    'null',
                ],
                '/\//' => [
                    'null',
                ],
            ]
        ]
    ],

    AllowAuthResolverAbstractFactory::KEY_ALLOW_AUTH_RESOLVER => [
        \rollun\permission\Auth\Middleware\AllowAuthResolver::class => [
            AllowAuthResolverAbstractFactory::KEY_ALLOW_AUTH => \rollun\permission\Comparator\AllowAuth::class
        ]
    ],

    AuthAdapterAbstractFactory::KEY_ADAPTER => [
        \rollun\permission\Auth\Adapter\BaseAuth::class => [
            AuthAdapterAbstractFactory::KEY_CLASS => \rollun\permission\Auth\Adapter\BaseAuth::class,
            AuthAdapterAbstractFactory::KEY_ADAPTER_CONFIG => [
                'accept_schemes' => 'basic',
                AuthAdapterAbstractFactory::KEY_AC_REALM => AuthAdapterAbstractFactory::DEFAULT_REALM,
                'nonce_timeout' => 3600,
            ]
        ],
        \rollun\permission\Auth\Adapter\OpenID::class => [
            AuthAdapterAbstractFactory::KEY_CLASS => \rollun\permission\Auth\Adapter\OpenID::class,
        ],
        \rollun\permission\Auth\Adapter\NullAdapter::class => [
            AuthAdapterAbstractFactory::KEY_CLASS => \rollun\permission\Auth\Adapter\NullAdapter::class,
        ]
    ],

    AuthenticationAbstractFactory::KEY_AUTHENTICATION => [
        'baseAuth' => [
            AuthenticationAbstractFactory::KEY_ADAPTER => 'baseAuthAdapter'
        ],
        'openId' => [
            AuthenticationAbstractFactory::KEY_ADAPTER => 'openIdAdapter'
        ],
    ],
];