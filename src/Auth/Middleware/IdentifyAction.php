<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 15.02.17
 * Time: 12:49
 */

namespace rollun\permission\Auth\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use rollun\datastore\DataStore\DataStoreAbstract;
use rollun\datastore\Rql\RqlQuery;
use Zend\Authentication\AuthenticationServiceInterface;
use Zend\Stratigility\MiddlewareInterface;

class IdentifyAction implements MiddlewareInterface
{

    const DEFAULT_IDENTITY = '0';

    const KEY_IDENTITY = 'identity';

    /** @var  AuthenticationServiceInterface */
    protected $authService;

    public function __construct(AuthenticationServiceInterface $authenticationService)
    {
        $this->authService = $authenticationService;
    }
    /**
     * @param Request $request
     * @param Response $response
     * @param null|callable $out
     * @return null|Response
     */
    public function __invoke(Request $request, Response $response, callable $out = null)
    {
        $identity = $this->authService->hasIdentity() ? $this->authService->getIdentity() : static::DEFAULT_IDENTITY;

        $request = $request->withAttribute(static::KEY_IDENTITY, $identity);

        if (isset($out)) {
            return $out($request,$response);
        }

        return $response;
    }
}
