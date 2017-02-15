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
use rollun\permission\Auth\Adapter\OpenIDAdapter;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\FactoryInterface;

class OpenIDFactory implements FactoryInterface
{

    const KEY_OPENID_RESOLVER = 'openIdResolver';

    const KEY_USER_DS_SERVICE = 'userDataStoreService';

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
        if (!isset($config[static::KEY_OPENID_RESOLVER][static::KEY_USER_DS_SERVICE])
            || $container->get($config[static::KEY_OPENID_RESOLVER][static::KEY_USER_DS_SERVICE])
        ) {
            return new ServiceNotFoundException(
                $config[static::KEY_OPENID_RESOLVER][static::KEY_USER_DS_SERVICE] . " not found."
            );
        }
        $webClient = $container->get(Web::class);
        $dataStore = $container->get($config[static::KEY_OPENID_RESOLVER][static::KEY_USER_DS_SERVICE]);
        return new OpenIDAdapter($webClient, $dataStore);
    }
}
