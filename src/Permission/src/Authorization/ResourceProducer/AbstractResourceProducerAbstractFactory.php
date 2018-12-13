<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\permission\Authorization\ResourceProducer;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;

/**
 * Abstract factories that extend this factory should have possibility
 * to create any instance of ResourceProducerInterface
 *
 * Class AbstractResourceProducerAbstractFactory
 * @package rollun\permission\Acl\ResourceProducer
 */
abstract class AbstractResourceProducerAbstractFactory implements AbstractFactoryInterface
{
    const KEY = self::class;

    const DEFAULT_CLASS = ResourceProducerInterface::class;

    const KEY_CLASS = 'class';

    /**
     * @param ContainerInterface $container
     * @param string $requestedName
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

    protected function getServiceConfig(ContainerInterface $container, $requestedName)
    {
        return $container->get('config')[self::KEY][static::class][$requestedName] ?? null;
    }
}
