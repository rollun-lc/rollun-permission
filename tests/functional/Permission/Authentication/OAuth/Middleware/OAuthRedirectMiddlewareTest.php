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
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use rollun\permission\OAuth\GoogleClient;
use rollun\permission\OAuth\RedirectMiddleware;

class OAuthRedirectMiddlewareTest extends TestCase
{
    private $scope = 'openid';

    private function createRedirectMiddleware(string $host, string $expectedRedirectUri = null): RedirectMiddleware
    {
        $urlHelper = $this->createMock(UrlHelper::class);
        $urlHelper->method('generate')->with('login')->willReturn('/login');

        $logger = $this->createMock(LoggerInterface::class);

        $googleClient = $this->getMockBuilder(GoogleClient::class)
            ->disableOriginalConstructor()
            ->getMock();

        if ($expectedRedirectUri !== null) {
            $googleClient->expects($this->once())
                ->method('setRedirectUri')
                ->with($expectedRedirectUri);
        }

        $googleClient->method('createAuthUrl')
            ->willReturn('https://accounts.google.com/o/oauth2/v2/auth?redirect_uri=stub');

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

    public function testProcessRedirectsToGoogle()
    {
        $object = $this->createRedirectMiddleware('http://localhost', 'http://localhost/login');
        $request = $this->createRequest('http://localhost/oauth/redirect');
        $handler = $this->createMock(RequestHandlerInterface::class);

        $response = $object->process($request, $handler);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertStringContainsString('accounts.google.com', $response->getHeader('Location')[0]);
    }

    public function testRedirectUriUsesRequestHostFromWhitelist()
    {
        $object = $this->createRedirectMiddleware(
            'http://primary.com,http://secondary.com',
            'http://secondary.com/login'
        );
        $request = $this->createRequest('http://secondary.com/oauth/redirect');
        $handler = $this->createMock(RequestHandlerInterface::class);

        $response = $object->process($request, $handler);

        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testRedirectUriFallsBackToFirstHostWhenRequestHostNotInWhitelist()
    {
        $object = $this->createRedirectMiddleware(
            'http://primary.com,http://secondary.com',
            'http://primary.com/login'
        );
        $request = $this->createRequest('http://unknown.com/oauth/redirect');
        $handler = $this->createMock(RequestHandlerInterface::class);

        $response = $object->process($request, $handler);

        $this->assertEquals(302, $response->getStatusCode());
    }
}
