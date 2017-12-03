<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 24.03.17
 * Time: 15:30
 */

namespace rollun\permission\Auth\Middleware\ErrorHandler\Factory;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use rollun\permission\Auth\Middleware\ErrorHandler\AccessForbiddenApiGwErrorResponseGenerator;
use rollun\permission\Auth\Middleware\ErrorHandler\AccessForbiddenErrorResponseGenerator;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\Stratigility\Middleware\ErrorHandler;

class ACLApiErrorHandlerFactory implements FactoryInterface
{

    const DEFAULT_ACL_ERROR_HANDLER = 'aclErrorHandler';
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
        $respGen = $container->get(AccessForbiddenApiGwErrorResponseGenerator::class);
        return new ErrorHandler(new EmptyResponse(), $respGen);
    }
}
