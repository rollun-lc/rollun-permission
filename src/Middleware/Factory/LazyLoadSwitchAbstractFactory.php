<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 16.02.17
 * Time: 14:08
 */

namespace rollun\permission\Middleware\Factory;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class LazyLoadSwitchAbstractFactory implements AbstractFactoryInterface
{
    const LAZY_LOAD_SWITCH = 'lazyLoadSwitch';

    const KEY_MIDDLEWARES_SERVICE = 'middlewares';

    const KEY_COMPARATOR_SERVICE = 'comparatorService';

    /**
     * Can the factory create an instance for the service?
     *
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @return bool
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        $config = $container->get('config');
        return isset($config[static::LAZY_LOAD_SWITCH][$requestedName]);
    }

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
        $config = $container->get('config');
        $factoryConfig = $config[static::LAZY_LOAD_SWITCH][$requestedName];
        $lazyLoadFactory =
            function (Request $request, Response $response, callable $out = null) use ($factoryConfig, $container, $requestedName) {
                $comparator = $container->get($factoryConfig[static::KEY_COMPARATOR_SERVICE]);
                foreach ($factoryConfig[static::KEY_MIDDLEWARES_SERVICE] as $pattern => $middlewareService) {
                    if ($comparator($request, $pattern)) {
                        if ($container->has($middlewareService)) {
                            $middleware = $container->get($middlewareService);
                            return $middleware($request, $response, $out);
                        } else {
                            throw new ServiceNotFoundException("Not found $middlewareService for $pattern pattern");
                        }
                    }
                }
                throw new ServiceNotCreatedException("Not found middleware for request");
            };
        return $lazyLoadFactory;
    }
}
