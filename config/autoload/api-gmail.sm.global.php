<?php

use rollun\api\Api\Google\Client\Web;
use rollun\api\Api\Google\Client\Cli as ApiGoogleClientCli;
use rollun\api\Api\Google\Client\Factory\AbstractFactory as ApiGoogleClientAbstractFactory;
use rollun\datastore\AbstractFactoryAbstract;

return [

    'services' => [
        'abstract_factories' => [
            'Zend\Db\Adapter\AdapterAbstractServiceFactory',
            ApiGoogleClientAbstractFactory::class
        ],
        'factories' => [
        ],
        'aliases' => [
            'wecClient' => Web::class,
        ],
    ],

    ApiGoogleClientAbstractFactory::KEY_GOOGLE_API_CLIENTS => [
        Web::class => [
            AbstractFactoryAbstract::KEY_CLASS => Web::class, //optionaly
            ApiGoogleClientAbstractFactory::KEY_SCOPES => [ //Must be set:
                'openid'
            ],
        ]
    ]
];
