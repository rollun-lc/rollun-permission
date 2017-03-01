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
use rollun\permission\Auth\Adapter\Interfaces\AuthenticatePrepareAdapterInterface;
use rollun\permission\Auth\Middleware\AuthenticationAction;
use rollun\permission\Auth\RuntimeException;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\FactoryInterface;

class AuthenticatePrepareDirectFactory implements FactoryInterface
{

    /**
     * Create an object
     *
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return object
     * @throws ServiceNotFoundException if unable to resolve the service.
     * @throws ServiceNotCreatedException if an exception is raised when
     *     creating a service.
     * @throws ContainerException if any other error occurs
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $resourceName = $requestedName;
        if (!$container->has($resourceName)) {
            throw new ServiceNotFoundException(
                'Can\'t make Middleware\DataStoreRest for resource: ' . $resourceName
            );
        }
        $authenticationMiddleware = null;
        $resourceObject = $container->get($resourceName);
        switch (true) {
            case is_a($resourceObject, AbstractWebAdapter::class, true) &&
                is_a($resourceObject, AuthenticatePrepareAdapterInterface::class, true):
                $authenticationMiddleware = new AuthenticationAction($resourceObject);
                break;
            case is_a($resourceObject, AuthenticationAction::class):
                $authenticationMiddleware = $resourceObject;
                break ;
            default:
                if (!isset($authenticationMiddleware)) {
                    throw new ServiceNotCreatedException(
                        'Can\'t make ' . AuthenticationAction::class
                        . ' for resource: ' . $resourceName
                    );
                }
        }
        return $authenticationMiddleware;
    }
}
