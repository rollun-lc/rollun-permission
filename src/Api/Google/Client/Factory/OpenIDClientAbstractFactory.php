<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 01.02.17
 * Time: 19:08
 */

namespace rollun\permission\Api\Google\Client\Factory;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use rollun\api\Api\Google\CliAbstractFactory;
use rollun\api\ApiException;
use rollun\permission\Api\Google\Client\OpenID;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Zend\Authentication\Storage\Session as SessionStorage;
use Zend\Session\Container;
use Zend\Session\SessionManager;

class OpenIDClientAbstractFactory implements AbstractFactoryInterface
{
    const GOOGLE_API_CLIENTS_SERVICES_KEY = 'GOOGLE_API_CLIENTS';
    const SCOPES = 'SCOPES';
    const CONFIG_KEY = 'CONFIG';
    const GOOGLE_CLIENT_CONFIG_KEYS = [ 'application_name', 'base_path',
        'client_id', 'client_secret', 'redirect_uri', 'state', 'developer_key',
        'use_application_default_credentials', 'signing_key', 'signing_algorithm',
        'subject', 'hd', 'prompt', 'openid.realm', 'include_granted_scopes',
        'login_hint', 'request_visible_actions', 'access_type', 'approval_prompt',
        'retry', 'cache_config', 'token_callback',
    ];

    /**
     * Can the factory create an instance for the service?
     *
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @return bool
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        $smConfig = $container->get('config');

        if (isset($smConfig[static::GOOGLE_API_CLIENTS_SERVICES_KEY][$requestedName])) {
            return true;
        } else {
            return false;
        }
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

        $smConfig = $container->get('config');
        $googleClientSmConfig = $smConfig[static::GOOGLE_API_CLIENTS_SERVICES_KEY][$requestedName];
        //Get class of Google Client - GoogleClient as default
        $requestedClassName = isset($googleClientSmConfig['class']) ? $googleClientSmConfig['class'] : OpenID::class;
        if (!is_a($requestedClassName, OpenID::class, true)) {
            throw new ApiException('Class $requestedClassName is not instance of ' . OpenID::class);
        }
        //Get config from Service Manager config
        $clientConfigFromSmConfig = isset($googleClientSmConfig[static::CONFIG_KEY]) ? $googleClientSmConfig[static::CONFIG_KEY] : [];
        $clientConfig = [];
        foreach ($clientConfigFromSmConfig as $key => $value) {
            if (in_array($key, static::GOOGLE_CLIENT_CONFIG_KEYS)) {
                $clientConfig[$key] = $value;
            } else {
                throw new ApiException('Wrong key in Google Client config: ' . $key);
            }
        }
        /* @var $client OpenID */
        $sessionManager = $container->get(SessionManager::class);
        $sessionContainer = new Container('SessionContainer', $sessionManager);

        $client = new $requestedClassName($clientConfig, $sessionContainer, null, $requestedName);

        //Get and set SCOPES
        $scopes = isset($googleClientSmConfig[static::SCOPES]) ? $googleClientSmConfig[static::SCOPES] : ['openid'];
        $client->setScopes($scopes);

        return $client;
    }
}
