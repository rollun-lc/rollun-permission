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
            'name' => 'login',
            'path' => '/login',
            'middleware' => 'authPipe',
            'allowed_methods' => ['GET', 'POST'],
        ],
        [
            'name' => 'logout',
            'path' => '/logout',
            'middleware' => \rollun\permission\Auth\Middleware\LogoutAction::class,
            'allowed_methods' => ['GET', 'POST'],
        ],
        [
            'name' => 'base-test-page',
            'path' => '/base/test-page/[{name}]',
            'middleware' => 'base-service',
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
