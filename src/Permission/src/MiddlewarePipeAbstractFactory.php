<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\permission;

use Interop\Container\ContainerInterface;
use InvalidArgumentException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;

/**
 * Create instance of MiddlewarePipe using 'config' service from ContainerInterface
 *
 * Config example:
 * <code>
 *  [
 *      MiddlewarePipeAbstractFactory::class => [
 *          'requestedServiceName' => [
 *              'class' => MiddlewarePipe::class
 *              'middlewares' => [
 *                  'middlewareServiceName1',
 *                  'middlewareServiceName2',
 *                  'middlewareServiceName3',
 *                  // ...
 *              ]
 *          ]
 *      ]
 *  ]
 * </code>
 *
 * Class MiddlewarePipeAbstractFactory
 * @package rollun\permission
 */
class MiddlewarePipeAbstractFactory implements AbstractFactoryInterface
{
    const KEY_MIDDLEWARES = 'middlewares';

    const KEY_CLASS = 'class';

    const DEFAULT_CLASS = MiddlewarePipe::class;

    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return MiddlewarePipe
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $middlewares = [];
        $config = $this->getServiceConfig($container, $requestedName);

        if (!isset($config[self::KEY_MIDDLEWARES])) {
            throw new InvalidArgumentException("Missing '" . self::KEY_MIDDLEWARES . "' option");
        }

        foreach ($config[self::KEY_MIDDLEWARES] as $key => $middleware) {
            if ($container->has($middleware)) {
                $middlewares[$key] = $container->get($middleware);
            } else {
                throw new ServiceNotFoundException("Middleware '$middleware' not found in container");
            }
        }

        ksort($middlewares);
        $class = $config[self::KEY_CLASS] ?? self::DEFAULT_CLASS;

        return new $class($middlewares);
    }

    /**
     * Can the factory create an instance for the service?
     *
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @return bool
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        $config = $this->getServiceConfig($container, $requestedName);

        if (!$config) {
            return false;
        }

        if ($class = $config[self::KEY_CLASS] ?? null) {
            return is_a($class, static::DEFAULT_CLASS, true);
        }

        return true;
    }

    public function getServiceConfig(ContainerInterface $container, $requestedName)
    {
        return $container->get('config')[self::class][$requestedName] ?? null;
    }
}
