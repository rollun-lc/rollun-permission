<?php
/**
 * Created by PhpStorm.
 * User: victorsecuring
 * Date: 03.12.17
 * Time: 2:21 PM
 */

namespace rollun\permission\Auth\SaveHandler\Factory;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use rollun\logger\Logger;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\Session\SaveHandler\DbTableGateway;
use Zend\Session\SaveHandler\DbTableGatewayOptions;

class DbTableSessionSaveHandlerFactory implements FactoryInterface
{
    const DEFAULT_SESSION_TABLE_NAME = "session";

    /**
     * Create an object
     *
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return DbTableGateway
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $tableGateway = $container->get(static::DEFAULT_SESSION_TABLE_NAME);
        $saveHandler = new DbTableGateway($tableGateway, new DbTableGatewayOptions());
        return $saveHandler;
    }
}