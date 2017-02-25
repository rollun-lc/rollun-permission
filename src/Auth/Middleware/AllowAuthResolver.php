<?php
/**
 * Created by PhpStorm.
 * User: victorsecuring
 * Date: 24.02.17
 * Time: 5:49 PM
 */

namespace rollun\permission\Auth\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use rollun\actionrender\Factory\LazyLoadSwitchAbstractFactory;
use rollun\dic\InsideConstruct;
use rollun\permission\Comparator\AllowAuth;
use Zend\Stratigility\MiddlewareInterface;

class AllowAuthResolver implements MiddlewareInterface
{

    const DEFAULT_ALLOW_AUTH = AllowAuth::class;

    const SELECTED_AUTH = 'selectedAuth';

    /** @var  AllowAuth */
    protected $allowAuth;

    /**
     * AllowAuthResolver constructor.
     * @param AllowAuth $allowAuth
     */
    public function __construct(AllowAuth $allowAuth)
    {
        InsideConstruct::setConstructParams(['allowAuth' => static::DEFAULT_ALLOW_AUTH]);
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
     */
    public function __invoke(Request $request, Response $response, callable $out = null)
    {
        $allowAuth = $this->allowAuth->getAllowAuth($request);

        $query = $request->getQueryParams();
        $selectedAuth = isset($query[static::SELECTED_AUTH]) ? $query[static::SELECTED_AUTH] : "null";

        $request = $request->withAttribute(LazyLoadSwitchAbstractFactory::DEFAULT_ATTRIBUTE_NAME, $allowAuth);

        if(isset($out)) {
            return $out($request, $response);
        }

        return $response;
    }
}