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
use rollun\permission\Auth\Middleware\Factory\AuthenticationAbstractFactory;

return [
    MiddlewarePipeAbstractFactory::KEY_AMP => [
        'identityPipe' => [
            'middlewares' => [
                \rollun\permission\Auth\Middleware\IdentifyAction::class,
            ]
        ],
        'loginPipe' => [
            'middlewares' => [
                rollun\permission\Auth\Middleware\AllowAuthResolver::class,
                'AuthAdapterSwitch',
            ]
        ],
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

    LazyLoadSwitchAbstractFactory::LAZY_LOAD_SWITCH => [
        'AuthAdapterSwitch' => [
            LazyLoadSwitchAbstractFactory::KEY_MIDDLEWARES_SERVICE => [
                'BaseAuth' => \rollun\permission\Auth\Adapter\BaseAuth::class,
                'OpenID' => \rollun\permission\Auth\Adapter\OpenID::class,
                'null' => \rollun\permission\Auth\Adapter\NullAdapter::class,
            ]
        ],
    ],

    AuthenticationAbstractFactory::KEY_AUTHENTICATION => [
        'baseAuth' => [
            AuthenticationAbstractFactory::KEY_ADAPTER => 'baseAuthAdapter'
        ],
        'openId' => [
            AuthenticationAbstractFactory::KEY_ADAPTER => 'openIdAdapter'
        ],
    ],

    ActionRenderAbstractFactory::KEY_AR_SERVICE => [
        'login-service' => [
            ActionRenderAbstractFactory::KEY_AR_MIDDLEWARE => [
                ActionRenderAbstractFactory::KEY_ACTION_MIDDLEWARE_SERVICE =>
                    'loginPipe',
                ActionRenderAbstractFactory::KEY_RENDER_MIDDLEWARE_SERVICE => 'simpleHtmlJsonRenderer'
            ]
        ],
    ]
];