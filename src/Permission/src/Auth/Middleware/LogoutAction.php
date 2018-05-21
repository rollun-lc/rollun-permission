<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 27.01.17
 * Time: 14:55
 */

namespace rollun\permission\Auth\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use rollun\dic\InsideConstruct;
use Zend\Authentication\AuthenticationServiceInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\JsonResponse;

class LogoutAction implements MiddlewareInterface
{
    /** @var AuthenticationServiceInterface */
    protected $authenticationService;

    /** @var  LoggerInterface */
    protected $logger;

    /**
     * LogoutAction constructor.
     * @param AuthenticationServiceInterface $authenticationService
     * @throws \ReflectionException
     */
    public function __construct(AuthenticationServiceInterface $authenticationService)
    {
        $this->authenticationService = $authenticationService;
        InsideConstruct::setConstructParams(["logger" => LoggerInterface::class]);
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * to the next middleware component to create the response.
     *
     * @param Request $request
     * @param DelegateInterface $delegate
     *
     * @return Response
     */
    public function process(Request $request, DelegateInterface $delegate)
    {
        if ($this->authenticationService->hasIdentity()) {
            $this->authenticationService->clearIdentity();
        }
        $this->logger->debug("In LogoutAction[" . microtime(true) . "]");
        $request = $request->withAttribute('responseData', ['status' => 'Logout complete!']);
        $response = $delegate->process($request);
        return $response;
    }
}
