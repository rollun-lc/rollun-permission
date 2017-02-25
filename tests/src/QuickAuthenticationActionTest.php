<?php
/**
 * Created by PhpStorm.
 * User: victorsecuring
 * Date: 24.02.17
 * Time: 5:25 PM
 */

namespace rollun\test\permission;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use rollun\permission\Auth\Adapter\AbstractWebAdapter;
use rollun\permission\Auth\Middleware\LazyAuthenticationAction;
use rollun\permission\Auth\Middleware\QuickAuthenticationAction;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Result;
use Zend\Diactoros\Request;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use Zend\Psr7Bridge\Psr7ServerRequest;


class QuickAuthenticationActionTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var LazyAuthenticationAction
     */
    protected $object;

    public function setUp()
    {

    }

    public function testLazyAuthenticationActionSuccess()
    {
        $webAdapter = $this->getMockBuilder(AbstractWebAdapter::class)->disableOriginalConstructor()->getMock();
        $authService = $this->getMock(AuthenticationService::class);

        $result = new Result(
            Result::SUCCESS,
            1,
            ['Invalid or absent credentials; challenging client']
        );

        $authService->method('hasIdentity')->will($this->returnValue(false));
        $authService->method('authenticate')->will($this->returnValue($result));

        $this->object = new QuickAuthenticationAction($webAdapter, $authService);

        $request =  new ServerRequest();
        $response = new Response();

        $this->object->__invoke($request, $response, function ($request, $response) {
            $identity = $request->getAttribute(LazyAuthenticationAction::KEY_IDENTITY);
            $this->assertEquals(1, $identity);

        });

    }

    public function testLazyAuthenticationActionFails()
    {
        $webAdapter = $this->getMockBuilder(AbstractWebAdapter::class)->disableOriginalConstructor()->getMock();

        $webAdapter->method('getRequest')->will($this->returnValue(new ServerRequest()));
        $authService = $this->getMock(AuthenticationService::class);

        $result = new Result(
            Result::FAILURE_CREDENTIAL_INVALID,
            null,
            ['Invalid or absent credentials; challenging client']
        );

        $authService->method('hasIdentity')->will($this->returnValue(false));
        $authService->method('authenticate')->will($this->returnValue($result));
        $this->object = new QuickAuthenticationAction($webAdapter, $authService);

        $request =  new ServerRequest();
        $response = new Response();
        $this->object->__invoke($request, $response, function ($request, $response) {
            $identity = $request->getAttribute(LazyAuthenticationAction::KEY_IDENTITY);
            $this->assertEquals(null, $identity);
        });
    }
}
