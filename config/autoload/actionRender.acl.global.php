<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 23.01.17
 * Time: 17:41
 */

use rollun\actionrender\Factory\MiddlewarePipeAbstractFactory;
use rollun\actionrender\Factory\ActionRenderAbstractFactory;
use rollun\actionrender\Renderer\ResponseRendererAbstractFactory;

return [
    'dependencies' => [
        'abstract_factories' => [

        ],
        'invokables' => [

        ],
        'factories' => [

        ],
    ],
    MiddlewarePipeAbstractFactory::KEY_AMP => [
        'aclPipes' => [
            'middlewares' => [
                \rollun\permission\Auth\Middleware\Identification::class,
                \rollun\permission\Acl\Middleware\ResourceResolver::class,
                \rollun\permission\Acl\Middleware\AclMiddleware::class,
            ]
        ]
    ],
];