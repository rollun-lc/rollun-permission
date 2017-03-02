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
            'path' => '/login/{resourceName}',
            'middleware' => 'loginServiceAR',
            'allowed_methods' => ['GET', 'POST'],
        ],
        [
            'name' => 'login-prepare-service',
            'path' => '/login_prepare/{resourceName}',
            'middleware' => 'loginPrepareServiceAR',
            'allowed_methods' => ['GET', 'POST'],
        ],
        [
            'name' => 'logout',
            'path' => '/logout',
            'middleware' => 'logoutAR',
            'allowed_methods' => ['GET', 'POST'],
        ],
        [
            'name' => 'user-page',
            'path' => '/user',
            'middleware' => 'user-page',
            'allowed_methods' => ['GET'],
        ],
        [
            'name' => 'home-page',
            'path' => '/[{name}]',
            'middleware' => 'home-page',
            'allowed_methods' => ['GET'],
        ],
    ],
];
