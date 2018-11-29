<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\permission\Auth\Middleware\ErrorHandler\Factory;

use Interop\Container\ContainerInterface;
use rollun\permission\Auth\Middleware\ErrorHandler\AccessForbiddenErrorResponseGenerator;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\Stratigility\Middleware\ErrorHandler;

/**
 * Create instance of ErrorHandler
 *
 * Class AclErrorHandlerFactory
 * @package rollun\permission\Auth\Middleware\ErrorHandler\Factory
 */
class AclErrorHandlerFactory implements FactoryInterface
{
    const DEFAULT_ACL_ERROR_HANDLER = 'aclErrorHandler';

    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return object|ErrorHandler
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $respGen = $container->get(AccessForbiddenErrorResponseGenerator::class);

        return new ErrorHandler(new EmptyResponse(), $respGen);
    }
}
