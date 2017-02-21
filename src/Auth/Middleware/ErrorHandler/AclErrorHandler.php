<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 21.02.17
 * Time: 14:48
 */

namespace rollun\permission\Auth\Middleware\ErrorHandler;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Zend\Stratigility\MiddlewarePipe;

class AclErrorHandler
{
    protected $errorMiddlewares;

    public function __construct(array $errorMiddlewares)
    {
        $this->errorMiddlewares = $errorMiddlewares;
    }

    public function __invoke($error, Request $request, Response $response, callable $next)
    {
        
    }
}
