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
    ],
];
