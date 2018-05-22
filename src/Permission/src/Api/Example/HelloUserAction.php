<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 16.01.17
 * Time: 12:26
 */

namespace rollun\permission\Api\Example;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use rollun\permission\Auth\Middleware\UserResolver;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Expressive\Template\TemplateRendererInterface;

class HelloUserAction implements MiddlewareInterface
{
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
        $data = [];
        $name = $request->getAttribute('name');
        $user = $request->getAttribute(UserResolver::KEY_ATTRIBUTE_USER);
        $data['user'] = $user;
        $data['str'] = "[" . constant('APP_ENV') . "] Hello $name!";

        if ($name === "error") {
            throw new \Exception("Exception by string: ".  $data['str']);
        }
        $request = $request->withAttribute('responseData', $data);
        $response = $delegate->process($request);
        return $response;
    }
}
