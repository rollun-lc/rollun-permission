<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 13.01.17
 * Time: 12:59
 */

namespace rollun\permission;

use rollun\actionrender\Factory\ActionRenderAbstractFactory;
use rollun\actionrender\Factory\LazyLoadPipeAbstractFactory;
use rollun\actionrender\Factory\MiddlewarePipeAbstractFactory;
use rollun\actionrender\Installers\ActionRenderInstaller;
use rollun\actionrender\Installers\BasicRenderInstaller;
use rollun\actionrender\Installers\LazyLoadPipeInstaller;
use rollun\actionrender\Installers\MiddlewarePipeInstaller;
use rollun\actionrender\ReturnMiddleware;
use rollun\permission\Auth\LazyLoadAuthMiddlewareGetter;
use rollun\permission\Auth\LazyLoadAuthPrepareMiddlewareGetter;
use rollun\api\Api\Google\Client\Installers\WebInstaller;
use rollun\installer\Install\InstallerAbstract;
use rollun\permission\Acl\Factory\AclFromDataStoreFactory;
use rollun\permission\Api\Example\HelloUserAction;
use rollun\permission\Auth\Adapter\BaseAuth;
use rollun\permission\Auth\Adapter\Factory\AuthAdapterAbstractFactory;
use rollun\permission\Auth\Adapter\GoogleOpenID;
use rollun\permission\Auth\Adapter\Session;
use rollun\permission\Auth\Middleware\ErrorHandler\AccessForbiddenHandlerMiddleware;
use rollun\permission\Auth\Middleware\ErrorHandler\Factory\AccessForbiddenHandlerFactory;
use rollun\permission\Auth\Middleware\Factory\IdentityFactory;
use rollun\permission\Auth\Middleware\Factory\LogoutActionFactory;
use rollun\permission\Auth\Middleware\Factory\UserResolverFactory;
use rollun\permission\Auth\Middleware\IdentityAction;
use rollun\permission\Auth\Middleware\LoginAction;
use rollun\permission\Auth\Middleware\LogoutAction;
use rollun\permission\Auth\Middleware\UserResolver;
use Zend\Session\Service\ContainerAbstractServiceFactory;
use Zend\Session\Service\SessionManagerFactory;
use Zend\Session\SessionManager;

class AuthInstaller extends InstallerAbstract
{

    /**
     * install
     * @return array
     */
    public function install()
    {
        $config = [
            'dependencies' => [
                'invokables' => [
                    LoginAction::class => LoginAction::class,
                    ReturnMiddleware::class => ReturnMiddleware::class,
                    HelloUserAction::class => HelloUserAction::class,
                    LazyLoadAuthMiddlewareGetter::class => LazyLoadAuthMiddlewareGetter::class,
                    LazyLoadAuthPrepareMiddlewareGetter::class => LazyLoadAuthPrepareMiddlewareGetter::class,
                ],
                'factories' => [
                    SessionManager::class => SessionManagerFactory::class,
                    IdentityAction::class => IdentityFactory::class,
                    LogoutAction::class => LogoutActionFactory::class,
                    UserResolver::class => UserResolverFactory::class,
                    AccessForbiddenHandlerMiddleware::class => AccessForbiddenHandlerFactory::class
                ],
                'abstract_factories' => [
                    AuthAdapterAbstractFactory::class,
                    ContainerAbstractServiceFactory::class,
                ]
            ],
            AuthAdapterAbstractFactory::KEY => [
                'GoogleOpenID' => [
                    AuthAdapterAbstractFactory::KEY_CLASS => GoogleOpenID::class,
                    AuthAdapterAbstractFactory::KEY_ADAPTER_CONFIG => [
                        'redirect_uri' => "http://" . constant("HOST") . "/login/GoogleOpenID/"
                    ],
                ],
                'BaseAuthIdentity' => [
                    AuthAdapterAbstractFactory::KEY_CLASS => BaseAuth::class
                ],
                'SessionIdentity' => [
                    AuthAdapterAbstractFactory::KEY_CLASS => Session::class
                ]
            ],
            LazyLoadPipeAbstractFactory::KEY => [
                'authenticateLLPipe' => LazyLoadAuthMiddlewareGetter::class,
                'authenticatePrepareLLPipe' => LazyLoadAuthPrepareMiddlewareGetter::class
            ],
            'session_containers' => [
                'WebSessionContainer'
            ],
            IdentityFactory::KEY => [
                IdentityFactory::KEY_ADAPTERS_SERVICE => [
                    'BaseAuthIdentity',
                    'SessionIdentity'
                ],
            ],
            UserResolverFactory::KEY => [
                UserResolverFactory::KEY_USER_DS_SERVICE => UserResolverFactory::DEFAULT_USER_DS,
                UserResolverFactory::KEY_ROLES_DS_SERVICE => AclFromDataStoreFactory::DEFAULT_ROLES_DS,
                UserResolverFactory::KEY_USER_ROLES_DS_SERVICE => UserResolverFactory::DEFAULT_USER_ROLES_DS,
            ],
            MiddlewarePipeAbstractFactory::KEY => [
                'loginServicePipe' => [
                    MiddlewarePipeAbstractFactory::KEY_MIDDLEWARES => [
                        'authenticateLLPipe'
                    ]
                ],
                'loginPrepareServicePipe' => [
                    MiddlewarePipeAbstractFactory::KEY_MIDDLEWARES => [
                        'authenticatePrepareLLPipe',
                        ReturnMiddleware::class
                    ]
                ],
                'identifyPipe' => [
                    MiddlewarePipeAbstractFactory::KEY_MIDDLEWARES => [
                        IdentityAction::class,
                        UserResolver::class
                    ]
                ]
            ],
            ActionRenderAbstractFactory::KEY => [
                'loginPageAR' => [
                    ActionRenderAbstractFactory::KEY_ACTION_MIDDLEWARE_SERVICE => LoginAction::class,
                    ActionRenderAbstractFactory::KEY_RENDER_MIDDLEWARE_SERVICE => 'simpleHtmlJsonRendererLLPipe',
                ],
                'logoutAR' => [
                    ActionRenderAbstractFactory::KEY_ACTION_MIDDLEWARE_SERVICE => LogoutAction::class,
                    ActionRenderAbstractFactory::KEY_RENDER_MIDDLEWARE_SERVICE => 'simpleHtmlJsonRendererLLPipe',
                ],
                'loginServiceAR' => [
                    ActionRenderAbstractFactory::KEY_ACTION_MIDDLEWARE_SERVICE => 'loginServicePipe',
                    ActionRenderAbstractFactory::KEY_RENDER_MIDDLEWARE_SERVICE => 'simpleHtmlJsonRendererLLPipe',
                ],
                'loginPrepareServiceAR' => [
                    ActionRenderAbstractFactory::KEY_ACTION_MIDDLEWARE_SERVICE => 'loginPrepareServicePipe',
                    ActionRenderAbstractFactory::KEY_RENDER_MIDDLEWARE_SERVICE => 'simpleHtmlJsonRendererLLPipe',
                ],
                'user-page' => [
                    ActionRenderAbstractFactory::KEY_ACTION_MIDDLEWARE_SERVICE => HelloUserAction::class,
                    ActionRenderAbstractFactory::KEY_RENDER_MIDDLEWARE_SERVICE => 'simpleHtmlJsonRendererLLPipe'
                ],
            ],
            'middleware_pipeline' => [
                'baseAuth' => [
                    'middleware' => [
                        'identifyPipe'
                    ],
                    'path' => '/',
                    'priority' => 9001,
                ],
                'error' => [
                    'middleware' => [
                        // Add error middleware here.
                        AccessForbiddenHandlerMiddleware::class,
                    ],
                    'error' => true,
                    'priority' => -10000,
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
            ],
        ];
        return $config;
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
            isset($config['dependencies']['invokables'][LoginAction::class]) &&
            isset($config['dependencies']['invokables'][ReturnMiddleware::class]) &&
            isset($config['dependencies']['invokables'][HelloUserAction::class]) &&
            isset($config['dependencies']['factories'][SessionManager::class]) &&
            isset($config['dependencies']['factories'][IdentityAction::class]) &&
            isset($config['dependencies']['factories'][LogoutAction::class]) &&
            isset($config['dependencies']['factories'][UserResolver::class]) &&
            isset($config['dependencies']['abstract_factories']) &&
            in_array(AuthAdapterAbstractFactory::class, $config['dependencies']['abstract_factories']) &&
            isset($config[AuthAdapterAbstractFactory::KEY]['GoogleOpenID']) &&
            isset($config[AuthAdapterAbstractFactory::KEY]['BaseAuthIdentity']) &&
            isset($config[AuthAdapterAbstractFactory::KEY]['SessionIdentity']) &&
            isset($config[LazyLoadPipeAbstractFactory::KEY]['authenticateLLPipe']) &&
            isset($config[LazyLoadPipeAbstractFactory::KEY]['authenticatePrepareLLPipe']) &&
            isset($config[IdentityFactory::KEY]) &&
            isset($config[UserResolverFactory::KEY]) &&
            isset($config[MiddlewarePipeAbstractFactory::KEY]['loginServicePipe']) &&
            isset($config[MiddlewarePipeAbstractFactory::KEY]['loginPrepareServicePipe']) &&
            isset($config[MiddlewarePipeAbstractFactory::KEY]['identifyPipe']) &&
            isset($config[ActionRenderAbstractFactory::KEY]['loginPageAR']) &&
            isset($config[ActionRenderAbstractFactory::KEY]['logoutAR']) &&
            isset($config[ActionRenderAbstractFactory::KEY]['loginServiceAR']) &&
            isset($config[ActionRenderAbstractFactory::KEY]['loginPrepareServiceAR']) &&
            isset($config[ActionRenderAbstractFactory::KEY]['user-page'])
        );
    }

    public function getDependencyInstallers()
    {
        return [
            MiddlewarePipeInstaller::class,
            ActionRenderInstaller::class,
            BasicRenderInstaller::class,
            LazyLoadPipeInstaller::class,
            WebInstaller::class,
        ];
    }
}
