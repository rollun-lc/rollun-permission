<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 02.02.17
 * Time: 17:48
 */

namespace rollun\permission\Auth\Middleware\ErrorHandler;

use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use rollun\permission\Acl\AccessForbiddenException;
use rollun\permission\Acl\Middleware\RoleResolver;
use rollun\permission\Auth\AlreadyLogginException;
use rollun\permission\Auth\CredentialInvalidException;
use rollun\permission\Auth\Middleware\IdentityAction;
use rollun\permission\Auth\Middleware\UserResolver;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Expressive\Helper\UrlHelper;

class AccessForbiddenErrorResponseGenerator
{
    /** @var  UrlHelper */
    protected $urlHelper;

    public function __construct(UrlHelper $urlHelper)
    {
        $this->urlHelper = $urlHelper;
    }

    public function __invoke(\Throwable $e, Request $request, Response $response)
    {
        if ($e instanceof AccessForbiddenException) {
            $user = $request->getAttribute(UserResolver::KEY_ATTRIBUTE_USER, ['roles' => [RoleResolver::DEFAULT_ROLE]]);
            if (empty(array_diff([RoleResolver::DEFAULT_ROLE], $user['roles']))) {
                $url = $this->urlHelper->generate('login-page');
                $response = new RedirectResponse($url, 302, ['Location' => filter_var($url, FILTER_SANITIZE_URL)]);
            } else {
                $response = new HtmlResponse('Access not granted.', 403);
            }
            return $response;
        } else if ($e instanceof AlreadyLogginException) {
            $url = $this->urlHelper->generate('home-page');
            $response = new RedirectResponse($url);
            return $response;
        } else if ($e instanceof CredentialInvalidException) {
            $response = new HtmlResponse("Invalid credentials!", 401);
            return $response;
        }

        return new HtmlResponse($this->errorPrint($e));
    }

    protected function errorPrint(Exception $e)
    {
        static $id;
        $id++;
        $message = "[$id]" . $e->getMessage() . "<br>";
        $message .= "file: [" . $e->getFile() . "]<br>" . "line: [" . $e->getLine() . "]<br>";
        $message .= "<br>";
        if (!is_null($e->getPrevious())) {
            $message .= $this->errorPrint($e->getPrevious());
        }
        return $message;
    }
}
