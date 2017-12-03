<?php
/**
 * Created by PhpStorm.
 * User: victorsecuring
 * Date: 03.12.17
 * Time: 3:04 PM
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

class AccessForbiddenApiGwErrorResponseGenerator
{
    public function __invoke(\Throwable $e, Request $request, Response $response)
    {
        if ($e instanceof AccessForbiddenException) {
            $response = new HtmlResponse('Access not granted.', 403);
            return $response;
        } else if ($e instanceof AlreadyLogginException) {
            $response = new HtmlResponse('Access not granted.', 403);
            return $response;
        } else if ($e instanceof CredentialInvalidException) {
            $response = new HtmlResponse("Invalid credentials!", 401);
            return $response;
        }

        return new HtmlResponse($this->errorPrint($e));
    }

    protected function errorPrint(\Throwable $e)
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