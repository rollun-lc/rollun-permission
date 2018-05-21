<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 01.03.17
 * Time: 19:01
 */

namespace rollun\permission\Auth\Middleware\Factory;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use rollun\permission\Auth\Adapter\AbstractWebAdapter;
use rollun\permission\Auth\Adapter\Interfaces\AuthenticateAdapterInterface;
use rollun\permission\Auth\Middleware\AuthenticationAction;
use rollun\permission\Auth\Middleware\RegisterAction;
use rollun\permission\Auth\RuntimeException;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class ImplicitRegisterAbstractFactory extends AbstractImplicitAbstractFactory
{

    /**
     * Create an object
     *
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return object
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $resourceName = $this->getResourceName($requestedName);
        $authenticationMiddleware = null;
        $resourceObject = $container->get($resourceName);
        switch (true) {
            case is_a($resourceObject, AbstractWebAdapter::class, true) &&
                is_a($resourceObject, AuthenticateAdapterInterface::class, true):
                $authenticationMiddleware = new RegisterAction($resourceObject);
                break;
            case is_a($resourceObject, RegisterAction::class):
                $authenticationMiddleware = $resourceObject;
                break ;
            default:
                if (!isset($authenticationMiddleware)) {
                    throw new ServiceNotCreatedException(
                        'Can\'t make ' . RegisterAction::class
                        . ' for resource: ' . $resourceName
                    );
                }
        }
        return $authenticationMiddleware;
    }

    /**
     * Return service postfix
     * @return string
     */
    static public function getImplicitPostfix()
    {
        return "RegisterMiddleware";
    }
}
