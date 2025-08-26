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
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use rollun\dic\InsideConstruct;
use rollun\permission\OAuth\GoogleClient;
use rollun\permission\OAuth\RedirectMiddleware;

class OAuthRedirectMiddlewareTest extends TestCase
{
    public function testProcess()
    {
        $container = require 'config/container.php';
        InsideConstruct::setContainer($container);

        $clientId = 'client-id';
        $projectId = 'rollun-test';
        $redirectUrl = 'http://localhost';
        $approvalPrompt = 'auto';
        $state = 'someState';
        $scope = 'openid';
        $accessType = 'online';
        $googleClientConfig = [
            'client_id'        => $clientId,
            'project_id'       => $projectId,
            'redirect_uri'     => $redirectUrl,
            'access_type'      => $accessType,
            'approval_prompt'  => $approvalPrompt,
            'state'            => $state,
        ];

        // Используем mock для UrlHelper, чтобы не зависеть от роутера
        $urlHelper = $this->createMock(UrlHelper::class);
        $urlHelper->expects($this->any())
            ->method('generate')
            ->with('login')
            ->willReturn('/login');

        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();

        $googleClient = new GoogleClient($googleClientConfig);
        $config = [
            'scopes'         => $scope,
            'host'           => $redirectUrl,
            'loginRouteName' => 'login',
        ];
        $object = new RedirectMiddleware(
            $googleClient,
            $urlHelper,
            $logger,
            $config
        );

        // Создаем mock сессии и добавляем ее в запрос
        $session = $this->createMock(SessionInterface::class);
        $request = (new ServerRequest())
            ->withQueryParams(['action' => 'login'])
            ->withAttribute(SessionMiddleware::SESSION_ATTRIBUTE, $session);

        /** @var RequestHandlerInterface $handler */
        $handler = $this->getMockBuilder(RequestHandlerInterface::class)->getMock();

        // Выполняем middleware
        $response = $object->process($request, $handler);

        // Проверяем, что ответ имеет статус 302 (Redirect)
        $this->assertEquals(302, $response->getStatusCode());

        // Получаем URL редиректа
        $location = $response->getHeader('Location')[0];

        // Разбираем URL на составные части
        $parsedUrl = parse_url($location);
        $this->assertEquals('https', $parsedUrl['scheme']);
        $this->assertEquals('accounts.google.com', $parsedUrl['host']);
        // Ожидаемый путь от Google OAuth (в актуальной версии используется /o/oauth2/v2/auth)
        $this->assertEquals('/o/oauth2/v2/auth', $parsedUrl['path']);

        // Разбираем query-параметры
        parse_str($parsedUrl['query'], $queryParams);

        $this->assertEquals('code', $queryParams['response_type']);
        $this->assertEquals($accessType, $queryParams['access_type']);
        $this->assertEquals($clientId, $queryParams['client_id']);
        // Проверяем, что redirect_uri соответствует http://localhost/login
        $this->assertEquals($redirectUrl . '/login', urldecode($queryParams['redirect_uri']));
        // Параметр state генерируется динамически – проверяем, что он не пустой
        $this->assertNotEmpty($queryParams['state']);
        // Проверяем scope
        $this->assertEquals($scope, $queryParams['scope']);
        // Если параметр approval_prompt не добавляется, можно проверить его отсутствие
        $this->assertArrayNotHasKey('approval_prompt', $queryParams);
    }
}
