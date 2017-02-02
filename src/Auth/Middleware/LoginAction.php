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
use rollun\permission\Acl\AccessForbiddenException;
use rollun\permission\Api\Google\Client\OpenID;
use rollun\permission\Auth\OpenIDAuthManager;
use Zend\Authentication\AuthenticationServiceInterface;
use Zend\Authentication\Result;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Expressive\Helper\UrlHelper;
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

    /** @var  OpenID */
    protected $googleClient;

    /** @var  UrlHelper */
    protected $urlHelper;

    public function __construct(Container $sessionContainer, OpenIDAuthManager $authManager, ClientAbstract $googleClient, UrlHelper $urlHelper)
    {
        $this->authManager = $authManager;
        $this->googleClient = $googleClient;
        $this->sessionContainer = $sessionContainer;
        $this->urlHelper = $urlHelper;
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
        $this->googleClient->initCode($request);
        if (($code = $this->googleClient->getAuthCode()) !== null) {
            $state = $this->sessionContainer->state;
            $result = $this->authManager->login($code, $state);
            if ($result->getCode() === Result::SUCCESS){
                $url = $request->getAttribute('redirectUrl') ?: $this->urlHelper->generate('home-page');
                $response = new RedirectResponse($url, 302, ['Location' => filter_var($url, FILTER_SANITIZE_URL)]);
            }else {
                throw new AccessForbiddenException("Auth credential error.");
            }
        } else {
            //add session status check
            //Проверять валидность и наличие токена должен authManager.
            //if(isset($this->sessionContainer->{static::KEY_ASSESS_TOKEN}))
            $state = sha1(openssl_random_pseudo_bytes(1024));
            $response = $this->googleClient->getCodeResponse($state);
        }
        $request = $request->withAttribute(Response::class, $response);

        if (isset($out)) {
            return $out($request, $response);
        }
        return $response;
    }
}
