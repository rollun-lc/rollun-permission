<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 13.01.17
 * Time: 12:59
 */

namespace rollun\permission\Installers;

use rollun\actionrender\Factory\ActionRenderAbstractFactory;
use rollun\actionrender\Factory\LazyLoadMiddlewareAbstractFactory;
use rollun\actionrender\Factory\MiddlewarePipeAbstractFactory;
use rollun\actionrender\Installers\ActionRenderInstaller;
use rollun\actionrender\Installers\BasicRenderInstaller;
use rollun\actionrender\Installers\LazyLoadMiddlewareInstaller;
use rollun\actionrender\Installers\MiddlewarePipeInstaller;
use rollun\actionrender\MiddlewareDeterminator\Factory\AbstractMiddlewareDeterminatorAbstractFactory;
use rollun\actionrender\MiddlewareDeterminator\Factory\AttributeParamAbstractFactory;
use rollun\actionrender\ReturnMiddleware;
use rollun\api\Api\Google\Client\Installers\WebInstaller;
use rollun\installer\Install\InstallerAbstract;
use rollun\permission\Acl\Factory\AclFromDataStoreFactory;
use rollun\permission\Api\Example\HelloUserAction;
use rollun\permission\Auth\Adapter\BaseAuth;
use rollun\permission\Auth\Adapter\Factory\AuthAdapterAbstractFactory;
use rollun\permission\Auth\Adapter\GoogleOpenID;
use rollun\permission\Auth\Adapter\Session;
use rollun\permission\Auth\AuthMiddlewareDeterminator;
use rollun\permission\Auth\AuthPrepareMiddlewareDeterminator;
use rollun\permission\Auth\Middleware\Factory\ImplicitAuthenticateAbstractFactory;
use rollun\permission\Auth\Middleware\Factory\ImplicitAuthenticatePrepareAbstractFactory;
use rollun\permission\Auth\Middleware\Factory\ImplicitRegisterAbstractFactory;
use rollun\permission\Auth\RegisterMiddlewareDeterminator;
use rollun\permission\Auth\Middleware\ErrorHandler\AccessForbiddenApiGwErrorResponseGenerator;
use rollun\permission\Auth\Middleware\ErrorHandler\AccessForbiddenErrorResponseGenerator;
use rollun\permission\Auth\Middleware\ErrorHandler\Factory\AccessForbiddenErrorResponseGeneratorFactory;
use rollun\permission\Auth\Middleware\ErrorHandler\Factory\ACLApiErrorHandlerFactory;
use rollun\permission\Auth\Middleware\ErrorHandler\Factory\AclErrorHandlerFactory;
use rollun\permission\Auth\Middleware\Factory\IdentityFactory;
use rollun\permission\Auth\Middleware\Factory\LogoutActionFactory;
use rollun\permission\Auth\Middleware\Factory\UserResolverFactory;
use rollun\permission\Auth\Middleware\IdentityAction;
use rollun\permission\Auth\Middleware\LoginAction;
use rollun\permission\Auth\Middleware\LogoutAction;
use rollun\permission\Auth\Middleware\UserResolver;
use Zend\ServiceManager\Factory\InvokableFactory;
use Zend\Session\SessionManager;

class AuthInstaller extends InstallerAbstract
{
    /**
     * install
     * @return array
     */
    public function install()
    {
        $errorHandlerFactory = [
            AccessForbiddenApiGwErrorResponseGenerator::class => InvokableFactory::class,
            AccessForbiddenErrorResponseGenerator::class => AccessForbiddenErrorResponseGeneratorFactory::class,
            AclErrorHandlerFactory::DEFAULT_ACL_ERROR_HANDLER => AclErrorHandlerFactory::class,
        ];

        if ($this->consoleIO->askConfirmation("You wont use API error handler ? ", false)) {
            $errorHandlerFactory[ACLApiErrorHandlerFactory::DEFAULT_ACL_ERROR_HANDLER] = ACLApiErrorHandlerFactory::class;
        }

        $config = [
            'dependencies' => [
                'invokables' => [
                    LoginAction::class => LoginAction::class,
                    ReturnMiddleware::class => ReturnMiddleware::class,
                    HelloUserAction::class => HelloUserAction::class,
                    AuthMiddlewareDeterminator::class => AuthMiddlewareDeterminator::class,
                    AuthPrepareMiddlewareDeterminator::class => AuthPrepareMiddlewareDeterminator::class,
                    RegisterMiddlewareDeterminator::class => RegisterMiddlewareDeterminator::class,
                ],
                'factories' => [
                    IdentityAction::class => IdentityFactory::class,
                    LogoutAction::class => LogoutActionFactory::class,
                    UserResolver::class => UserResolverFactory::class,
                ],
                'abstract_factories' => [
                    AuthAdapterAbstractFactory::class,
                    ImplicitAuthenticatePrepareAbstractFactory::class,
                    ImplicitAuthenticateAbstractFactory::class,
                    ImplicitRegisterAbstractFactory::class,
                ],
            ],
            AuthAdapterAbstractFactory::KEY => [
                'GoogleRegisterOpenID' => [
                    AuthAdapterAbstractFactory::KEY_CLASS => GoogleOpenID::class,
                    AuthAdapterAbstractFactory::KEY_ADAPTER_CONFIG => [
                        'redirect_uri' => 'http://\'.  constant("HOST") .\'/register/GoogleRegisterOpenID',
                    ],
                ],
                'GoogleOpenID' => [
                    AuthAdapterAbstractFactory::KEY_CLASS => GoogleOpenID::class,
                    AuthAdapterAbstractFactory::KEY_ADAPTER_CONFIG => [
                        'redirect_uri' => 'http://\'.  constant("HOST") .\'/login/GoogleOpenID',
                    ],
                ],
                'BaseAuthIdentity' => [
                    AuthAdapterAbstractFactory::KEY_CLASS => BaseAuth::class,
                ],
                'SessionIdentity' => [
                    AuthAdapterAbstractFactory::KEY_CLASS => Session::class,
                ],
            ],

            AbstractMiddlewareDeterminatorAbstractFactory::KEY => [
                RegisterMiddlewareDeterminator::class => [
                    AttributeParamAbstractFactory::KEY_CLASS => RegisterMiddlewareDeterminator::class,
                    AttributeParamAbstractFactory::KEY_NAME => "resourceName",
                ],
                AuthPrepareMiddlewareDeterminator::class => [
                    AttributeParamAbstractFactory::KEY_CLASS => AuthPrepareMiddlewareDeterminator::class,
                    AttributeParamAbstractFactory::KEY_NAME => "resourceName",
                ],
                AuthMiddlewareDeterminator::class => [
                    AttributeParamAbstractFactory::KEY_CLASS => AuthMiddlewareDeterminator::class,
                    AttributeParamAbstractFactory::KEY_NAME => "resourceName",
                ],
            ],
            LazyLoadMiddlewareAbstractFactory::KEY => [
                'registerLLPipe' => [
                    LazyLoadMiddlewareAbstractFactory::KEY_MIDDLEWARE_DETERMINATOR => RegisterMiddlewareDeterminator::class,
                ],
                'authenticatePrepareLLPipe' => [
                    LazyLoadMiddlewareAbstractFactory::KEY_MIDDLEWARE_DETERMINATOR => AuthPrepareMiddlewareDeterminator::class,
                ],
                'authenticateLLPipe' => [
                    LazyLoadMiddlewareAbstractFactory::KEY_MIDDLEWARE_DETERMINATOR => AuthMiddlewareDeterminator::class,
                ],
            ],
            IdentityFactory::KEY => [
                IdentityFactory::KEY_ADAPTERS_SERVICE => [
                    'BaseAuthIdentity',
                    'SessionIdentity',
                ],
            ],
            UserResolverFactory::KEY => [
                UserResolverFactory::KEY_USER_DS_SERVICE => UserResolverFactory::DEFAULT_USER_DS,
                UserResolverFactory::KEY_ROLES_DS_SERVICE => AclFromDataStoreFactory::DEFAULT_ROLES_DS,
                UserResolverFactory::KEY_USER_ROLES_DS_SERVICE => UserResolverFactory::DEFAULT_USER_ROLES_DS,
            ],
            MiddlewarePipeAbstractFactory::KEY => [
                'permissionPipe' => [
                    MiddlewarePipeAbstractFactory::KEY_MIDDLEWARES => [
                        AclErrorHandlerFactory::DEFAULT_ACL_ERROR_HANDLER,
                        'identifyPipe',
                        'aclPipes',
                    ],
                ],
                'registerServicePipe' => [
                    MiddlewarePipeAbstractFactory::KEY_MIDDLEWARES => [
                        'registerLLPipe',
                    ],
                ],
                'loginServicePipe' => [
                    MiddlewarePipeAbstractFactory::KEY_MIDDLEWARES => [
                        'authenticateLLPipe',
                    ],
                ],
                'loginPrepareServicePipe' => [
                    MiddlewarePipeAbstractFactory::KEY_MIDDLEWARES => [
                        'authenticatePrepareLLPipe',
                        ReturnMiddleware::class,
                    ],
                ],
                'identifyPipe' => [
                    MiddlewarePipeAbstractFactory::KEY_MIDDLEWARES => [
                        IdentityAction::class,
                        UserResolver::class,
                    ],
                ],
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
                'registerServiceAR' => [
                    ActionRenderAbstractFactory::KEY_ACTION_MIDDLEWARE_SERVICE => 'registerServicePipe',
                    ActionRenderAbstractFactory::KEY_RENDER_MIDDLEWARE_SERVICE => 'simpleHtmlJsonRendererLLPipe',
                ],
                'loginPrepareServiceAR' => [
                    ActionRenderAbstractFactory::KEY_ACTION_MIDDLEWARE_SERVICE => 'loginPrepareServicePipe',
                    ActionRenderAbstractFactory::KEY_RENDER_MIDDLEWARE_SERVICE => 'simpleHtmlJsonRendererLLPipe',
                ],
                'user-page' => [
                    ActionRenderAbstractFactory::KEY_ACTION_MIDDLEWARE_SERVICE => HelloUserAction::class,
                    ActionRenderAbstractFactory::KEY_RENDER_MIDDLEWARE_SERVICE => 'simpleHtmlJsonRendererLLPipe',
                ],
            ],
        ];
        $config['dependencies']['factories'] = array_merge_recursive(
            $config['dependencies']['factories'],
            $errorHandlerFactory
        );

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

        return (isset($config['dependencies']['invokables'][LoginAction::class])
            && isset($config['dependencies']['invokables'][ReturnMiddleware::class])
            && isset($config['dependencies']['invokables'][HelloUserAction::class])
            && isset($config['dependencies']['factories'][SessionManager::class])
            && isset($config['dependencies']['factories'][IdentityAction::class])
            && isset($config['dependencies']['factories'][LogoutAction::class])
            && isset($config['dependencies']['factories'][UserResolver::class])
            && isset($config['dependencies']['abstract_factories'])
            && in_array(AuthAdapterAbstractFactory::class, $config['dependencies']['abstract_factories'])
            && isset($config[AuthAdapterAbstractFactory::KEY]['GoogleOpenID'])
            && isset($config[AuthAdapterAbstractFactory::KEY]['BaseAuthIdentity'])
            && isset($config[AuthAdapterAbstractFactory::KEY]['SessionIdentity'])
            && isset($config[IdentityFactory::KEY])
            && isset($config[UserResolverFactory::KEY])
            && isset($config[MiddlewarePipeAbstractFactory::KEY]['loginServicePipe'])
            && isset($config[MiddlewarePipeAbstractFactory::KEY]['loginPrepareServicePipe'])
            && isset($config[MiddlewarePipeAbstractFactory::KEY]['identifyPipe'])
            && isset($config[ActionRenderAbstractFactory::KEY]['loginPageAR'])
            && isset($config[ActionRenderAbstractFactory::KEY]['logoutAR'])
            && isset($config[ActionRenderAbstractFactory::KEY]['loginServiceAR'])
            && isset($config[ActionRenderAbstractFactory::KEY]['loginPrepareServiceAR'])
            && isset($config[ActionRenderAbstractFactory::KEY]['user-page']));
    }

    public function getDependencyInstallers()
    {
        return [
            SessionInstaller::class,
            MiddlewarePipeInstaller::class,
            ActionRenderInstaller::class,
            BasicRenderInstaller::class,
            LazyLoadMiddlewareInstaller::class,
            WebInstaller::class,
        ];
    }
}
