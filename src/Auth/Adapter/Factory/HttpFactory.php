<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 15.02.17
 * Time: 15:37
 */

namespace rollun\permission\Auth\Adapter\Factory;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Zend\Authentication\Adapter\Http;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\FactoryInterface;

class HttpFactory implements FactoryInterface
{
    const KEY_HTTP_ADAPTER = 'httpAdapter';

    const KEY_ADAPTER_CONFIG = 'config';

    const KEY_BASIC_RESOLVER = 'basicResolver';

    const KEY_DIGEST_RESOLVER = 'digestResolver';

    const KEY_AC_REALM = 'realm';

    const DEFAULT_REALM = 'RollunService';



    /**
     * Create an object
     *
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return object
     * @throws \Exception
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');
        if (isset($config[static::KEY_HTTP_ADAPTER])) {
            $factoryConfig = $config[static::KEY_HTTP_ADAPTER];
            $adapterConfig = isset($factoryConfig[static::KEY_ADAPTER_CONFIG]) ?
                $factoryConfig[static::KEY_ADAPTER_CONFIG] :
                [
                    'accept_schemes' => 'basic',
                    static::KEY_AC_REALM => static::DEFAULT_REALM,
                    'nonce_timeout' => 3600,
                ];
            $http = new Http($adapterConfig);
            if (isset($factoryConfig[static::KEY_BASIC_RESOLVER]) &&
                $container->has($factoryConfig[static::KEY_BASIC_RESOLVER])
            ) {
                $basicResolver = $container->get($factoryConfig[static::KEY_BASIC_RESOLVER]);
                $http->setBasicResolver($basicResolver);
            }
            if (isset($factoryConfig[static::KEY_DIGEST_RESOLVER]) &&
                $container->has($factoryConfig[static::KEY_DIGEST_RESOLVER])
            ) {
                $digestResolver = $container->get($factoryConfig[static::KEY_DIGEST_RESOLVER]);
                $http->setDigestResolver($digestResolver);
            }
            return $http;
        }
        throw new \Exception("Config not set.");
    }
}
