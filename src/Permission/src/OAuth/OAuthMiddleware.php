<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\permission\OAuth;

use InvalidArgumentException;
use Mezzio\Authentication\Session\Exception\MissingSessionContainerException;
use Mezzio\Helper\UrlHelper;
use Mezzio\Session\SessionInterface;
use Mezzio\Session\SessionMiddleware;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Log\LoggerInterface;

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
     * @param ServerRequestInterface|null $request
     * @return string|null
     */
    protected function actionToRedirectUri($action, ServerRequestInterface $request = null): ?string
    {
        $routeNameConfig = $action . 'RouteName';

        if (!$this->getConfig($routeNameConfig)) {
            throw new InvalidArgumentException("Missing '$routeNameConfig' config for redirect");
        }

        $host = $this->resolveHost($request);

        $this->logger->debug('actionToRedirectUri', [
            'config' => $this->config,
            'action' => $action,
            'routeNameConfig' => $routeNameConfig,
            'host' => $host,
            'path' => $this->urlHelper->generate($this->getConfig($routeNameConfig))
        ]);

        return $host . $this->urlHelper->generate($this->getConfig($routeNameConfig));
    }

    /**
     * Resolve host from request, validated against whitelist from config.
     * Config 'host' may contain comma-separated whitelist (e.g. "https://host1.com,https://host2.com").
     * First entry is used as fallback.
     *
     * @param ServerRequestInterface|null $request
     * @return string
     */
    private function resolveHost(ServerRequestInterface $request = null): string
    {
        $hostConfig = $this->getConfig('host');

        if (!$hostConfig) {
            throw new InvalidArgumentException("Missing 'host' config for redirect");
        }

        $whitelist = array_map('trim', explode(',', $hostConfig));
        $fallback = $whitelist[0];

        if (!$request) {
            return $fallback;
        }

        $requestHost = $request->getHeaderLine('X-Forwarded-Host')
            ?: $request->getHeaderLine('Host')
            ?: $request->getUri()->getHost();

        foreach ($whitelist as $entry) {
            if (parse_url($entry, PHP_URL_HOST) === $requestHost) {
                return $entry;
            }
        }

        $this->logger->debug('resolveHost: request host not in whitelist, using fallback', [
            'requestHost' => $requestHost,
            'whitelist' => $whitelist,
            'fallback' => $fallback,
        ]);

        return $fallback;
    }
}
