<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 13.02.17
 * Time: 17:33
 */

namespace rollun\permission\Auth\Middleware\ErrorHandler;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use rollun\permission\Auth\AlreadyLogginException;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Expressive\Helper\UrlHelper;


class AlreadyLogginHandler
{

    /** @var  UrlHelper */
    protected $urlHelper;

    public function __construct(UrlHelper $urlHelper)
    {
        $this->urlHelper = $urlHelper;
    }

    public function __invoke($error, Request $request, Response $response, callable $next) {
        if ($error instanceof AlreadyLogginException) {
            $url = $this->urlHelper->generate('home-page');
            $response = new RedirectResponse($url);
        }

        if (isset($next)) {
            return $next($request, $response);
        }

        return $response;
    }
}
