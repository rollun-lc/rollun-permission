<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 01.03.17
 * Time: 14:12
 */

use rollun\actionrender\Factory\ActionRenderAbstractFactory;
use rollun\actionrender\Factory\MiddlewarePipeAbstractFactory;

return [
    ActionRenderAbstractFactory::KEY_AR => [
        'loginPageAR' => [
            ActionRenderAbstractFactory::KEY_AR_MIDDLEWARE => [
                ActionRenderAbstractFactory::KEY_ACTION_MIDDLEWARE_SERVICE => '',
                ActionRenderAbstractFactory::KEY_RENDER_MIDDLEWARE_SERVICE => 'simpleHtmlJsonRenderer',
            ]
        ],
        'logoutAR' => [
            ActionRenderAbstractFactory::KEY_AR_MIDDLEWARE => [
                ActionRenderAbstractFactory::KEY_ACTION_MIDDLEWARE_SERVICE => '',
                ActionRenderAbstractFactory::KEY_RENDER_MIDDLEWARE_SERVICE => 'simpleHtmlJsonRenderer',
            ]
        ]
    ],

    MiddlewarePipeAbstractFactory::KEY_AMP => [
        'loginServicePipe' => [
            MiddlewarePipeAbstractFactory::KEY_MIDDLEWARES => [

            ]
        ],
        'loginPrepareServicePipe' => [
            MiddlewarePipeAbstractFactory::KEY_MIDDLEWARES => [

            ]
        ],
        'identifyPipe' => [
            MiddlewarePipeAbstractFactory::KEY_MIDDLEWARES => [

            ]
        ]
    ]
];
