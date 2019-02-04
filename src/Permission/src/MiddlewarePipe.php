<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\permission;

use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Stratigility\MiddlewarePipe as StratigilityMiddlewarePipe;

class MiddlewarePipe implements MiddlewareInterface
{
    /**
     * @var StratigilityMiddlewarePipe
     */
    protected $middlewarePipe;

    /**
     * MiddlewarePipe constructor.
     * @param array|MiddlewareInterface[] $middlewares
     */
    public function __construct(array $middlewares)
    {
        $this->middlewarePipe = new StratigilityMiddlewarePipe();

        foreach ($middlewares as $middleware) {
            if ($middleware instanceof MiddlewareInterface) {
                $this->middlewarePipe->pipe($middleware);
            } else {
                throw new InvalidArgumentException(
                    'Expected instance of ' . MiddlewareInterface::class . ', instance of ' . gettype($middleware)
                    . ' given'
                );
            }
        }
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $this->middlewarePipe->process($request, $handler);

        return $response;
    }
}
