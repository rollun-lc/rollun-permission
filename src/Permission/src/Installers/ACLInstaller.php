<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 13.01.17
 * Time: 12:59
 */

namespace rollun\permission\Installers;

use rollun\actionrender\Factory\MiddlewarePipeAbstractFactory;
use rollun\actionrender\Installers\MiddlewarePipeInstaller;
use rollun\datastore\DataStore\Installers\CacheableInstaller;
use rollun\installer\Command;
use rollun\installer\Install\InstallerAbstract;
use rollun\permission\Acl\Factory\AclFromDataStoreFactory;
use rollun\permission\Acl\Middleware\AclMiddleware;
use rollun\permission\Acl\Middleware\Factory\AclMiddlewareFactory;
use rollun\permission\Acl\Middleware\Factory\ResourceResolverFactory;
use rollun\permission\Acl\Middleware\Factory\RoleResolverFactory;
use rollun\permission\Acl\Middleware\PrivilegeResolver;
use rollun\permission\Acl\Middleware\ResourceResolver;
use rollun\permission\Acl\Middleware\RoleResolver;
use rollun\permission\Auth\Middleware\ErrorHandler\AccessForbiddenErrorResponseGenerator;
use rollun\permission\Auth\Middleware\ErrorHandler\Factory\AccessForbiddenErrorResponseGeneratorFactory;
use rollun\permission\Auth\Middleware\ErrorHandler\Factory\ACLErrorHandlerFactory;
use Zend\Permissions\Acl\Acl;
use Zend\Session\Service\SessionManagerFactory;
use Zend\Session\SessionManager;

class ACLInstaller extends InstallerAbstract
{

    /**
     * install
     * @return array
     */
    public function install()
    {
        return [
            'acl' => [
                AclFromDataStoreFactory::KEY_DS_RULE_SERVICE => AclFromDataStoreFactory::DEFAULT_RULES_DS,
                AclFromDataStoreFactory::KEY_DS_ROLE_SERVICE => AclFromDataStoreFactory::DEFAULT_ROLES_DS,
                AclFromDataStoreFactory::KEY_DS_RESOURCE_SERVICE => AclFromDataStoreFactory::DEFAULT_RESOURCE_DS,
                AclFromDataStoreFactory::KEY_DS_PRIVILEGE_SERVICE => AclFromDataStoreFactory::DEFAULT_PRIVILEGE_DS,
            ],
            'dependencies' => [
                'invokables' => [
                    PrivilegeResolver::class => PrivilegeResolver::class
                ],
                'factories' => [
                    SessionManager::class => SessionManagerFactory::class,
                    Acl::class => AclFromDataStoreFactory::class,
                    ResourceResolver::class => ResourceResolverFactory::class,
                    RoleResolver::class => RoleResolverFactory::class,
                    AclMiddleware::class => AclMiddlewareFactory::class,
                ],
            ],
            MiddlewarePipeAbstractFactory::KEY => [
                'aclPipes' => [
                    'middlewares' => [
                        RoleResolver::class,
                        ResourceResolver::class,
                        PrivilegeResolver::class,
                        AclMiddleware::class,
                    ]
                ]
            ],
        ];
    }

    /**
     * Clean all installation
     * @return void
     */
    public function uninstall()
    {

    }

    /**
     * Return string with description of installable functional.
     * @param string $lang ; set select language for description getted.
     * @return string
     */
    public function getDescription($lang = "en")
    {
        switch ($lang) {
            case "ru":
                $description = "Предоставляет pipe middleware для работы ACL.";
                break;
            default:
                $description = "Does not exist.";
        }
        return $description;
    }

    public function isInstall()
    {
        $config = $this->container->get('config');
        return (
            isset($config['acl'][AclFromDataStoreFactory::KEY_DS_RULE_SERVICE]) &&
            isset($config['acl'][AclFromDataStoreFactory::KEY_DS_ROLE_SERVICE]) &&
            isset($config['acl'][AclFromDataStoreFactory::KEY_DS_RESOURCE_SERVICE]) &&
            isset($config['acl'][AclFromDataStoreFactory::KEY_DS_PRIVILEGE_SERVICE]) &&
            isset($config['dependencies']['invokables'][PrivilegeResolver::class]) &&
            isset($config['dependencies']['factories'][SessionManager::class]) &&
            isset($config['dependencies']['factories'][Acl::class]) &&
            isset($config['dependencies']['factories'][ResourceResolver::class ]) &&
            isset($config['dependencies']['factories'][RoleResolver::class]) &&
            isset($config['dependencies']['factories'][AclMiddleware::class]) &&
            isset($config[MiddlewarePipeAbstractFactory::KEY]['aclPipes'])
        );
    }

    public function getDependencyInstallers()
    {
        return [
            MiddlewarePipeInstaller::class
        ];
    }
}
