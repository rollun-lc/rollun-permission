<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 16.02.17
 * Time: 15:26
 */

namespace rollun\permission\Auth\Adapter\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;

abstract class AdapterAbstractFactoryAbstract implements AbstractFactoryInterface
{
    const KEY_AC_REALM = 'realm';

    const DEFAULT_REALM = 'RollunService';

    const KEY_ADAPTER = 'adapter';

    const KEY_ADAPTER_CONFIG = 'config';

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
        return isset($config[static::KEY_ADAPTER][$requestedName]);
    }
}
