<?php

return [
    'dependencies' => [
        'invokables' => [
            Zend\Expressive\Router\RouterInterface::class => Zend\Expressive\Router\FastRouteRouter::class,
           /* \rollun\permission\Api\OAuth2Action::class => \rollun\permission\Api\OAuth2Action::class,
            \rollun\permission\Api\OAuth2RedirectAction::class => \rollun\permission\Api\OAuth2RedirectAction::class,*/
            //\rollun\permission\Api\ServiceAuthAction::class => \rollun\permission\Api\ServiceAuthAction::class,
        ],
        'factories' => [
        ],
    ],

    'routes' => [
        [
            'name' => 'login-page',
            'path' => '/login',
            'middleware' => 'loginPageAR',
            'allowed_methods' => ['GET', 'POST'],
        ],
        [
            'name' => 'login-service',
            'path' => '/login/{adapterName}',
            'middleware' => 'loginService',
            'allowed_methods' => ['GET', 'POST'],
        ],
        [
            'name' => 'login-prepare-service',
            'path' => '/login_prepare/{adapterName}',
            'middleware' => 'loginPrepareService',
            'allowed_methods' => ['GET', 'POST'],
        ],
        [
            'name' => 'logout',
            'path' => '/logout',
            'middleware' => 'logoutAR',
            'allowed_methods' => ['GET', 'POST'],
        ],
        [
            'name' => 'home-page',
            'path' => '/[{name}]',
            'middleware' => 'home-service',
            'allowed_methods' => ['GET'],
        ],
    ],
];
