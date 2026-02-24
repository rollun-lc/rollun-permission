<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\test\functional\Permission\Authentication\OAuth\Middleware;

use Google_Client;
use Laminas\Diactoros\ServerRequest;
use Mezzio\Helper\UrlHelper;
use Mezzio\Session\SessionInterface;
use Mezzio\Session\SessionMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use rollun\permission\OAuth\GoogleClient;
use rollun\permission\OAuth\RedirectMiddleware;

class OAuthRedirectMiddlewareTest extends TestCase
{
    private $clientId = 'client-id';
    private $scope = 'openid';
    private $accessType = 'online';

    private function createGoogleClientMock(): GoogleClient
    {
        $redirectUri = null;
        $state = null;

        $googleClient = $this->getMockBuilder(GoogleClient::class)
            ->disableOriginalConstructor()
            ->getMock();

        $googleClient->method('setRedirectUri')->willReturnCallback(function ($uri) use (&$redirectUri) {
            $redirectUri = $uri;
        });
        $googleClient->method('getRedirectUri')->willReturnCallback(function () use (&$redirectUri) {
            return $redirectUri;
        });
        $googleClient->method('setState')->willReturnCallback(function ($s) use (&$state) {
            $state = $s;
        });
        $googleClient->method('setScopes')->willReturn(null);

        $googleClient->method('createAuthUrl')->willReturnCallback(function () use (&$redirectUri, &$state) {
            $params = [
                'response_type' => 'code',
                'access_type' => $this->accessType,
                'client_id' => $this->clientId,
                'redirect_uri' => $redirectUri,
                'state' => $state,
                'scope' => $this->scope,
            ];
            return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
        });

        return $googleClient;
    }

    private function createRedirectMiddleware(string $host): RedirectMiddleware
    {
        $urlHelper = $this->createMock(UrlHelper::class);
        $urlHelper->expects($this->any())
            ->method('generate')
            ->with('login')
            ->willReturn('/login');

        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $googleClient = $this->createGoogleClientMock();

        $config = [
            'scopes'         => $this->scope,
            'host'           => $host,
            'loginRouteName' => 'login',
        ];

        return new RedirectMiddleware($googleClient, $urlHelper, $logger, $config);
    }

    private function createRequest(string $uri): ServerRequestInterface
    {
        $session = $this->createMock(SessionInterface::class);

        return (new ServerRequest([], [], $uri))
            ->withQueryParams(['action' => 'login'])
            ->withAttribute(SessionMiddleware::SESSION_ATTRIBUTE, $session);
    }

    private function getRedirectUriFromResponse(ResponseInterface $response): string
    {
        $location = $response->getHeader('Location')[0];
        $parsedUrl = parse_url($location);
        parse_str($parsedUrl['query'], $queryParams);

        return urldecode($queryParams['redirect_uri']);
    }

    public function testProcess()
    {
        $redirectUrl = 'http://localhost';
        $object = $this->createRedirectMiddleware($redirectUrl);
        $request = $this->createRequest('http://localhost/oauth/redirect');

        /** @var RequestHandlerInterface $handler */
        $handler = $this->getMockBuilder(RequestHandlerInterface::class)->getMock();

        $response = $object->process($request, $handler);

        $this->assertEquals(302, $response->getStatusCode());

        $location = $response->getHeader('Location')[0];

        $parsedUrl = parse_url($location);
        $this->assertEquals('https', $parsedUrl['scheme']);
        $this->assertEquals('accounts.google.com', $parsedUrl['host']);
        $this->assertEquals('/o/oauth2/v2/auth', $parsedUrl['path']);

        parse_str($parsedUrl['query'], $queryParams);

        $this->assertEquals('code', $queryParams['response_type']);
        $this->assertEquals($this->accessType, $queryParams['access_type']);
        $this->assertEquals($this->clientId, $queryParams['client_id']);
        $this->assertEquals($redirectUrl . '/login', urldecode($queryParams['redirect_uri']));
        $this->assertNotEmpty($queryParams['state']);
        $this->assertEquals($this->scope, $queryParams['scope']);
        $this->assertArrayNotHasKey('approval_prompt', $queryParams);
    }

    public function testRedirectUriUsesRequestHostFromWhitelist()
    {
        $object = $this->createRedirectMiddleware('http://primary.com,http://secondary.com');
        $request = $this->createRequest('http://secondary.com/oauth/redirect');
        $handler = $this->getMockBuilder(RequestHandlerInterface::class)->getMock();

        $response = $object->process($request, $handler);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('http://secondary.com/login', $this->getRedirectUriFromResponse($response));
    }

    public function testRedirectUriFallsBackToFirstHostWhenRequestHostNotInWhitelist()
    {
        $object = $this->createRedirectMiddleware('http://primary.com,http://secondary.com');
        $request = $this->createRequest('http://unknown.com/oauth/redirect');
        $handler = $this->getMockBuilder(RequestHandlerInterface::class)->getMock();

        $response = $object->process($request, $handler);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('http://primary.com/login', $this->getRedirectUriFromResponse($response));
    }
}
