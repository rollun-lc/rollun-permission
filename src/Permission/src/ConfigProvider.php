<?php

namespace rollun\permission;

use rollun\actionrender\Factory\MiddlewarePipeAbstractFactory;
use rollun\permission\Acl\Middleware\AclMiddleware;
use rollun\permission\Acl\Middleware\PrivilegeResolver;
use rollun\permission\Acl\Middleware\ResourceResolver;
use rollun\permission\Acl\Middleware\RoleResolver;
use rollun\permission\Auth\AuthenticationServiceChainAbstractFactory;
use rollun\permission\Auth\BasicAccessAbstractFactory;
use rollun\permission\Auth\Middleware\ErrorHandler\Factory\AclErrorHandlerFactory;
use Zend\Expressive\Authentication\AuthenticationInterface;
use Zend\Expressive\Authentication\AuthenticationMiddleware;
use Zend\Expressive\Authentication\UserRepository\Htpasswd;
use Zend\Expressive\Authentication\UserRepository\HtpasswdFactory;

/**
 * The configuration provider for the App module
 *
 * @see https://docs.zendframework.com/zend-component-installer/
 */
class ConfigProvider
{
    /**
     * Returns the configuration array
     *
     * To add a bit of a structure, each section is defined in a separate
     * method which returns an array with its configuration.
     *
     * @return array
     */
    public function __invoke()
    {
        return [
            'dependencies' => $this->getDependencies(),
            'templates' => $this->getTemplates(),
            'authentication' => $this->getAuthenticationConfig(),
            MiddlewarePipeAbstractFactory::KEY => $this->getMiddlewarePipeConfig(),
            AuthenticationServiceChainAbstractFactory::KEY => $this->getAuthenticationServiceChainConfig(),
            BasicAccessAbstractFactory::KEY => $this->getBasicAccessConfig(),
        ];
    }

    public function getAuthenticationConfig()
    {
        return [
            'htpasswd' => [

            ]
        ];
    }

    /**
     * Returns the container dependencies
     *
     * @return array
     */
    public function getDependencies()
    {
        return [
            'abstract_factories' => [
                AuthenticationServiceChainAbstractFactory::class,
                BasicAccessAbstractFactory::class,
            ],
            'factories' => [
                Htpasswd::class => HtpasswdFactory::class
            ],
            'aliases' => [
                AuthenticationInterface::class => 'basicAccess'
            ]
        ];
    }

    public function getBasicAccessConfig()
    {
        return [
            'basicAccess' => [
                'realm' => 'realmValue',
                'userRepository' => Htpasswd::class
            ]
        ];
    }

    public function getAuthenticationServiceChainConfig()
    {
        return [
            'authenticationServiceChain' => [
                'authenticationServices' => [

                ]
            ],
        ];
    }

    public function getMiddlewarePipeConfig()
    {
        return [
            'permissionPipe' => [
                MiddlewarePipeAbstractFactory::KEY_MIDDLEWARES => [
                    AclErrorHandlerFactory::DEFAULT_ACL_ERROR_HANDLER,
                    AuthenticationMiddleware::class,
                    'aclPipe',
                ],
            ],
            'aclPipe' => [
                MiddlewarePipeAbstractFactory::KEY_MIDDLEWARES => [
                    RoleResolver::class,
                    ResourceResolver::class,
                    PrivilegeResolver::class,
                    AclMiddleware::class,
                ],
            ],
        ];
    }

    /**
     * Returns the templates configuration
     *
     * @return array
     */
    public function getTemplates()
    {
        return [
            'paths' => [
                'app' => [__DIR__ . '/../templates/auth'],
                'layout' => [__DIR__ . '/../templates/layout'],
            ],
        ];
    }
}
