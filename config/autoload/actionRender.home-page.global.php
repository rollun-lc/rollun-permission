<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 24.01.17
 * Time: 17:32
 */

use rollun\actionrender\Factory\ActionRenderAbstractFactory;

return [
    'dependencies' => [
        'invokables' => [
            \rollun\permission\Api\HelloUserAction::class => \rollun\permission\Api\HelloUserAction::class,
        ],
    ],
    ActionRenderAbstractFactory::KEY_AR_SERVICE => [
        'home-service' => [
            ActionRenderAbstractFactory::KEY_AR_MIDDLEWARE => [
                ActionRenderAbstractFactory::KEY_ACTION_MIDDLEWARE_SERVICE =>
                    \rollun\permission\Api\HelloUserAction::class,
                ActionRenderAbstractFactory::KEY_RENDER_MIDDLEWARE_SERVICE => 'simpleHtmlJsonRenderer'
            ]
        ],
    ],

];