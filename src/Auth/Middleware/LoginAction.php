<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 27.01.17
 * Time: 14:56
 */

namespace rollun\permission\Auth\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use rollun\api\Api\Google\ClientAbstract;
use rollun\permission\Auth\OpenIDAuthManager;
use Zend\Authentication\AuthenticationServiceInterface;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Session\Container;
use Zend\Session\SessionManager;
use Zend\Stratigility\MiddlewareInterface;

class LoginAction implements MiddlewareInterface
{

    /** @var  AuthenticationServiceInterface */
    protected $authService;

    /** @var  OpenIDAuthManager */
    protected $authManager;

    /** @var  Container */
    protected $sessionContainer;

    /** @var  ClientAbstract */
    protected $googleClient;

    public function __construct(Container $sessionContainer, OpenIDAuthManager $authManager, ClientAbstract $googleClient)
    {
        $this->authManager = $authManager;
        $this->googleClient = $googleClient;
        $this->sessionContainer = $sessionContainer;
    }

    /**
     * Process an incoming request and/or response.
     *
     * Accepts a server-side request and a response instance, and does
     * something with them.
     *
     * If the response is not complete and/or further processing would not
     * interfere with the work done in the middleware, or if the middleware
     * wants to delegate to another process, it can use the `$out` callable
     * if present.
     *
     * If the middleware does not return a value, execution of the current
     * request is considered complete, and the response instance provided will
     * be considered the response to return.
     *
     * Alternately, the middleware may return a response instance.
     *
     * Often, middleware will `return $out();`, with the assumption that a
     * later middleware will return a response.
     *
     * @param Request $request
     * @param Response $response
     * @param null|callable $out
     * @return null|Response
     * @throws \Exception
     */
    public function __invoke(Request $request, Response $response, callable $out = null)
    {
        $queryParams = $request->getQueryParams();

        if (isset($queryParams['code'])) {
            //add check if exists...
            //ПРоверка соответсвия стейта тоже должна лежать на AuthManager

        } else {
            //add session status check
            //Проверять валидность и наличие токена должен authManager.
            //if(isset($this->sessionContainer->{static::KEY_ASSESS_TOKEN}))
            $state = sha1(openssl_random_pseudo_bytes(1024));
            $this->sessionContainer->{static::KEY_STATE} = $state;
            $this->googleClient->setState($state);

            $authUrl = $this->googleClient->createAuthUrl();

            $header = array_merge($response->getHeaders(), ['Location' => filter_var($authUrl, FILTER_SANITIZE_URL)]);
            $response = new RedirectResponse($authUrl, 302, $header);
            $request = $request->withAttribute(Response::class, $response);
        }

        if (isset($out)) {
            return $out($request, $response);
        }

        return $response;
    }
}
