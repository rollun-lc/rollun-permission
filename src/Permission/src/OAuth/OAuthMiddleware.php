<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\permission\OAuth;

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Log\LoggerInterface;
use Zend\Expressive\Authentication\Session\Exception\MissingSessionContainerException;
use Zend\Expressive\Helper\UrlHelper;
use Zend\Expressive\Session\SessionInterface;
use Zend\Expressive\Session\SessionMiddleware;

abstract class OAuthMiddleware implements MiddlewareInterface
{
    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var array
     */
    private $config;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var GoogleClient
     */
    protected $googleClient;

    /**
     * @var UrlHelper
     */
    protected $urlHelper;

    /**
     * Suppose that application can have several middleware using oauth: login, register, logout, redirect, etc.
     * In most cases these middlewares can have oauth client, logger, url helper (to resolve routes) and configs.
     *
     * OAuthMiddleware constructor.
     * @param GoogleClient $googleClient
     * @param UrlHelper $urlHelper
     * @param LoggerInterface $logger
     * @param array $config
     */
    public function __construct(GoogleClient $googleClient, UrlHelper $urlHelper, LoggerInterface $logger, $config = [])
    {
        $this->config = $config;
        $this->logger = $logger;
        $this->googleClient = $googleClient;
        $this->urlHelper = $urlHelper;
    }

    /**
     * Fetch session from request attributes
     *
     * @param ServerRequestInterface $request
     * @return SessionInterface
     */
    protected function getSession(ServerRequestInterface $request): SessionInterface
    {
        if ($this->session === null) {
            $this->session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);

            if (!$this->session instanceof SessionInterface) {
                throw MissingSessionContainerException::create();
            }
        }

        return $this->session;
    }

    /**
     * @param $key
     * @param null $default
     * @return mixed|null
     */
    protected function getConfig($key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * @param $action
     * @return string|null
     */
    protected function actionToRedirectUri($action): ?string
    {
        $routeNameConfig = $action . 'RouteName';

        foreach ([$routeNameConfig, 'host'] as $config) {
            if (!$this->getConfig($config)) {
                throw new InvalidArgumentException("Missing '$config' config for redirect");
            }
        }
        $this->logger->debug('actionToRedirectUri', [
            'config' => $this->config,
            'action' => $action,
            'routeNameConfig' => $routeNameConfig,
            'host' => $this->getConfig('host'),
            'path' => $this->urlHelper->generate($this->getConfig($routeNameConfig))
        ]);

        return $this->getConfig('host') . $this->urlHelper->generate($this->getConfig($routeNameConfig));
    }
}
