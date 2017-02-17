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
        /*[
            'name' => 'home',
            'path' => '/',
            'middleware' => \rollun\permission\Api\OAuth2Action::class,
            'allowed_methods' => ['GET'],
        ],
        [
            'name' => 'oAuth2r',
            'path' => '/oauth2r',
            'middleware' => \rollun\permission\Api\OAuth2RedirectAction::class,
            'allowed_methods' => ['GET'],
        ],*/
        /*[
            'name' => 'service',
            'path' => '/service',
            'middleware' => \rollun\permission\Api\ServiceAuthAction::class,
            'allowed_methods' => ['GET'],
        ],*/
        /*
         * if you use rollun-datastore uncomment this. and add Config.
         [
            'name' => 'api.rest',
            'path' => '/api/rest[/{resourceName}[/{id}]]',
            'middleware' => 'api-rest',
            'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'],
         ],
        */
        /*
         * if you use rollun-callback uncomment this. and add Config.
         [
            'name' => 'interrupt.callback',
            'path' => '/interrupt/callback',
            'middleware' => 'interrupt-callback',
            'allowed_methods' => ['POST'],
         ],
         */
        /*[
            'name' => 'openidr',
            'path' => '/openidr',
            'middleware' => \rollun\permission\Api\OpenIDRequestAction::class,
            'allowed_methods' => ['GET'],
        ],
        [
            'name' => 'openid',
            'path' => '/openid',
            'middleware' => \rollun\permission\Api\OpenIDConnectAction::class,
            'allowed_methods' => ['GET'],
        ],*/
        [
            'name' => 'interrupt_cron',
            'path' => '/interrupt/cron',
            'middleware' => \rollun\permission\Api\CronExceptionMiddleware::class,
            'allowed_methods' => ['GET', 'POST'],
        ],
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
