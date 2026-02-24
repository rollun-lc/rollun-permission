<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\test\functional\Permission\Authentication\OAuth\Middleware;

use Laminas\Diactoros\ServerRequest;
use Mezzio\Helper\UrlHelper;
use Mezzio\Session\SessionInterface;
use Mezzio\Session\SessionMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use rollun\dic\InsideConstruct;
use rollun\permission\OAuth\GoogleClient;
use rollun\permission\OAuth\RedirectMiddleware;

class OAuthRedirectMiddlewareTest extends TestCase
{
    private $clientId = 'client-id';
    private $projectId = 'rollun-test';
    private $approvalPrompt = 'auto';
    private $state = 'someState';
    private $scope = 'openid';
    private $accessType = 'online';

    private function createRedirectMiddleware(string $host): RedirectMiddleware
    {
        $googleClientConfig = [
            'client_id'        => $this->clientId,
            'project_id'       => $this->projectId,
            'redirect_uri'     => 'http://localhost',
            'access_type'      => $this->accessType,
            'approval_prompt'  => $this->approvalPrompt,
            'state'            => $this->state,
        ];

        $urlHelper = $this->createMock(UrlHelper::class);
        $urlHelper->expects($this->any())
            ->method('generate')
            ->with('login')
            ->willReturn('/login');

        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();

        $googleClient = new GoogleClient($googleClientConfig);
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
        $container = require 'config/container.php';
        InsideConstruct::setContainer($container);

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
        $container = require 'config/container.php';
        InsideConstruct::setContainer($container);

        $object = $this->createRedirectMiddleware('http://primary.com,http://secondary.com');
        $request = $this->createRequest('http://secondary.com/oauth/redirect');
        $handler = $this->getMockBuilder(RequestHandlerInterface::class)->getMock();

        $response = $object->process($request, $handler);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('http://secondary.com/login', $this->getRedirectUriFromResponse($response));
    }

    public function testRedirectUriFallsBackToFirstHostWhenRequestHostNotInWhitelist()
    {
        $container = require 'config/container.php';
        InsideConstruct::setContainer($container);

        $object = $this->createRedirectMiddleware('http://primary.com,http://secondary.com');
        $request = $this->createRequest('http://unknown.com/oauth/redirect');
        $handler = $this->getMockBuilder(RequestHandlerInterface::class)->getMock();

        $response = $object->process($request, $handler);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('http://primary.com/login', $this->getRedirectUriFromResponse($response));
    }
}
