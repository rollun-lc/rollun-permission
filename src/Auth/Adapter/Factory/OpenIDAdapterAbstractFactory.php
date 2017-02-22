<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 02.02.17
 * Time: 15:47
 */

namespace rollun\permission\Auth\Adapter\Factory;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use rollun\api\Api\Google\Client\Web;
use rollun\permission\Auth\Adapter\OpenID;
use rollun\permission\Auth\Adapter\OpenIDAdapter;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class OpenIDAdapterAbstractFactory extends AdapterAbstractFactoryAbstract
{

    const KEY_ADAPTER = 'openIdAdapter';

    const KEY_RESOLVER = 'resolver';

    const KEY_WEB_CLIENT = 'webClient';

    const DEFAULT_WEB_CLIENT = Web::class;

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
        $factoryConfig = $config[static::KEY_ADAPTER][$requestedName];
        $adapterConfig = isset($factoryConfig[static::KEY_ADAPTER_CONFIG]) ?
            $factoryConfig[static::KEY_ADAPTER_CONFIG] :
            [static::KEY_AC_REALM => static::DEFAULT_REALM];

        $openIdAdapter = new OpenID($adapterConfig);

        if(isset($adapterConfig[static::KEY_WEB_CLIENT])) {
            if(!$container->has($adapterConfig[static::KEY_WEB_CLIENT])) {
                throw new ServiceNotFoundException($adapterConfig[static::KEY_WEB_CLIENT] . " service not found.");
            }
            $webClient = $container->get($adapterConfig[static::KEY_WEB_CLIENT]);
        } else {
            $webClient = $container->get(static::DEFAULT_WEB_CLIENT);
        }
        $openIdAdapter->setWebClient($webClient);

        if (isset($factoryConfig[static::KEY_RESOLVER]) &&
            $container->has($factoryConfig[static::KEY_RESOLVER])
        ) {
            $basicResolver = $container->get($factoryConfig[static::KEY_RESOLVER]);
            $openIdAdapter->setResolver($basicResolver);
        }
        return $openIdAdapter;
    }
}
