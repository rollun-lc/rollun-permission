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
use rollun\api\Api\Google\Client\Web;
use rollun\permission\Acl\AccessForbiddenException;
use rollun\permission\Auth\CredentialInvalidException;
use rollun\permission\Auth\Manager\OpenIDAuth;
use Zend\Authentication\AuthenticationServiceInterface;
use Zend\Authentication\Result;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Expressive\Helper\UrlHelper;
use Zend\Session\Container;
use Zend\Session\SessionManager;
use Zend\Stratigility\MiddlewareInterface;

class LoginAction implements MiddlewareInterface
{
    /** @var  OpenIDAuth */
    protected $authManager;

    /** @var  Web */
    protected $webClient;

    /** @var  UrlHelper */
    protected $urlHelper;

    /**
     * LoginAction constructor.
     * @param OpenIDAuth $authManager
     * @param Web $webClient
     * @param UrlHelper $urlHelper
     */
    public function __construct(
        OpenIDAuth $authManager,
        Web $webClient,
        UrlHelper $urlHelper){
        $this->authManager = $authManager;
        $this->webClient = $webClient;
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
        //$urlHelper = $request->getAttribute(UrlHelper::class);

        $query = $request->getQueryParams();
        $code = isset($query['code'])? $query['code'] : null;
        if (isset($code)) {
            $state = isset($query['state']) ? $query['state'] : "";
            $result = $this->authManager->login($code, $state);
            if ($result->getCode() === Result::SUCCESS) {
                $identity = $result->getIdentity();
                $url = $request->getAttribute('redirectUrl') ?: $this->urlHelper->generate('home-page', ['name' => $identity['name']]);
                $response = new RedirectResponse($url, 302, ['Location' => filter_var($url, FILTER_SANITIZE_URL)]);
            } else {
                throw new CredentialInvalidException("Auth credential error.");
            }
        } else {
            $state = sha1(openssl_random_pseudo_bytes(1024));
            $response = $this->webClient->getAuthCodeRedirect($state);
        }
        $request = $request->withAttribute(Response::class, $response);

        if (isset($out)) {
            return $out($request, $response);
        }

        return $response;
    }
}
