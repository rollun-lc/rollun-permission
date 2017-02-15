<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 02.02.17
 * Time: 17:48
 */

namespace rollun\permission\Auth\Middleware\ErrorHandler;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use rollun\permission\Acl\AccessForbiddenException;
use rollun\permission\Acl\Middleware\RoleResolver;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Expressive\Helper\UrlHelper;

class AccessForbiddenHandlerMiddleware
{
    /** @var  UrlHelper */
    protected $urlHelper;

    public function __construct(UrlHelper $urlHelper)
    {
        $this->urlHelper = $urlHelper;
    }

    public function __invoke($error, Request $request, Response $response, callable $next) {
        if ($error instanceof AccessForbiddenException) {
            if ($request->getAttribute('role') === RoleResolver::DEFAULT_ROLE) {
                $url = $this->urlHelper->generate('login');
                $response = new RedirectResponse($url, 302, ['Location' => filter_var($url, FILTER_SANITIZE_URL)]);
            } else {
                $response = new HtmlResponse("Access not granted.");
            }
            return $response;
        }

        if (isset($next)) {
            return $next($request, $response);
        }

        return $response;
    }
}
