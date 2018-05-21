<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 13.01.17
 * Time: 12:59
 */

namespace rollun\permission\Installers;

use rollun\actionrender\Factory\ActionRenderAbstractFactory;
use rollun\actionrender\Factory\LazyLoadPipeAbstractFactory;
use rollun\actionrender\Factory\MiddlewarePipeAbstractFactory;
use rollun\actionrender\Installers\ActionRenderInstaller;
use rollun\actionrender\Installers\BasicRenderInstaller;
use rollun\actionrender\Installers\LazyLoadPipeInstaller;
use rollun\actionrender\Installers\MiddlewarePipeInstaller;
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
use rollun\permission\Auth\RegisterMiddlewareDeterminator;
use rollun\permission\Auth\Middleware\ErrorHandler\AccessForbiddenApiGwErrorResponseGenerator;
use rollun\permission\Auth\Middleware\ErrorHandler\AccessForbiddenErrorResponseGenerator;
use rollun\permission\Auth\Middleware\ErrorHandler\Factory\AccessForbiddenErrorResponseGeneratorFactory;
use rollun\permission\Auth\Middleware\ErrorHandler\Factory\ACLApiErrorHandlerFactory;
use rollun\permission\Auth\Middleware\ErrorHandler\Factory\ACLErrorHandlerFactory;
use rollun\permission\Auth\Middleware\Factory\IdentityFactory;
use rollun\permission\Auth\Middleware\Factory\LogoutActionFactory;
use rollun\permission\Auth\Middleware\Factory\UserResolverFactory;
use rollun\permission\Auth\Middleware\IdentityAction;
use rollun\permission\Auth\Middleware\LoginAction;
use rollun\permission\Auth\Middleware\LogoutAction;
use rollun\permission\Auth\Middleware\UserResolver;
use rollun\permission\Auth\SaveHandler\Factory\DbTableSessionSaveHandlerFactory;
use Zend\ServiceManager\Factory\InvokableFactory;
use Zend\Session\SaveHandler\SaveHandlerInterface;
use Zend\Session\Service\SessionManagerFactory;
use Zend\Session\SessionManager;
use Zend\Session\Storage\SessionArrayStorage;

class SessionInstaller extends InstallerAbstract
{

    /**
     * install
     * @return array
     */
    public function install()
    {

        //ask for session saveHandler type
        $session = [
            'dependencies' => [
                'invokables' => [
                ],
                'factories' => [
                ],
                'abstract_factories' => [
                ]
            ],
            'session_config' => [
                'cookie_lifetime' => 60 * 60 * 2,
                "gc_maxlifetime" => 60 * 60 * 2,
            ],
            'session_storage' => [
                "type" => SessionArrayStorage::class,
            ],
            "session_manager" => [
                "validators" => [
                    #\Zend\Session\Validator\RemoteAddr::class,
                    #\Zend\Session\Validator\HttpUserAgent::class,
                ]
            ],
            'session_containers' => [
                'WebSessionContainer'
            ],
        ];

        if($this->consoleIO->askConfirmation("You wont use DbTable session save handler ? ")) {
            $session['dependencies']['factories'][SaveHandlerInterface::class] = DbTableSessionSaveHandlerFactory::class;
        }

        return $session;
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
                $description = "Предоставляет настройки для сессии.";
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
            isset($config['session_config']) &&
            isset($config['session_storage']) &&
            isset($config['session_manager']) &&
            isset($config['session_containers'])
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
