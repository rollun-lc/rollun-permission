<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\test\functional\Permission\Authentication\OAuth\Middleware;

use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;
use rollun\permission\OAuth\GoogleClient;
use rollun\permission\OAuth\RedirectMiddleware;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Diactoros\ServerRequest;

class OAuthRedirectMiddlewareTest extends TestCase
{
    public function testProcess()
    {
        $clientId = 'client-id';
        $projectId = 'rollun-test';
        $redirectUrl = 'http://localhost';
        $approvalPrompt = 'auto';
        $state = 'someState';
        $scope = 'openid';
        $accessType = 'online';
        $googleClientConfig = [
            'client_id' => $clientId,
            'project_id' => $projectId,
            'redirect_uri' => $redirectUrl,
            'access_type' => $accessType,
            'approval_prompt' => $approvalPrompt,
            'state' => $state,
        ];

        $googleClient = new GoogleClient($googleClientConfig);
        $object = new RedirectMiddleware($googleClient, $scope);
        $request = new ServerRequest();

        /** @var RequestHandlerInterface $handler */
        $handler = $this->getMockBuilder(RequestHandlerInterface::class)->getMock();
        $responseAssert = new RedirectResponse(
            "https://accounts.google.com/o/oauth2/auth?response_type=code"
            . "&access_type=" . urlencode($accessType)
            . "&client_id=" . urlencode($clientId)
            . "&redirect_uri=" . urlencode($redirectUrl)
            . "&state=" . urlencode($state)
            . "&scope=" . urlencode($scope)
            . "&approval_prompt=" . urlencode($approvalPrompt)
        );

        $response = $object->process($request, $handler);
        $this->assertEquals($responseAssert->getStatusCode(), $response->getStatusCode());
        $this->assertEquals($responseAssert->getHeader('Location'), $response->getHeader('Location'));
    }
}
