<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\permission\OAuth;

use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\RedirectResponse;

class RedirectMiddleware extends OAuthMiddleware
{
    const KEY_ACTION = 'action';

    /**
     * @var array
     */
    protected $config;

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $redirectUri = $this->getRedirectUri($request);

        if (!$redirectUri) {
            $this->logger->debug("Missing required '" . self::KEY_ACTION . "' query param");

            return (new Response())->withStatus(400);
        }

        if (!$this->getConfig('scopes')) {
            throw new InvalidArgumentException("Missing 'scopes' config for redirect");
        }

        $state = sha1(openssl_random_pseudo_bytes(1024));
        $this->getSession($request)->set(GoogleClient::KEY_STATE, $state);

        $this->googleClient->setRedirectUri($redirectUri);
        $this->googleClient->setState($state);
        $this->googleClient->setScopes($this->getConfig('scopes'));

        $authUrl = filter_var($this->googleClient->createAuthUrl(), FILTER_SANITIZE_URL);

        $this->logger->debug(
            json_encode(
                [
                    'Auth url: ' . $authUrl,
                    'State on redirect: ' . $state,
                    'Scopes on redirect: ' . json_encode($this->getConfig('scopes')),
                ]
            )
        );

        return new RedirectResponse($authUrl);
    }

    /**
     * @param ServerRequestInterface $request
     * @return string|null
     */
    protected function getRedirectUri(ServerRequestInterface $request): ?string
    {
        $action = $request->getQueryParams()[self::KEY_ACTION] ?? null;


        if (!in_array($action, [LoginMiddleware::ACTION, RegisterMiddleware::ACTION])) {
            return null;
        }

        $url = $this->actionToRedirectUri($action);

        $this->logger->debug('getRedirectUri', [
            'action' => $action,
            'url' => $url
        ]);

        return $url;
    }
}
