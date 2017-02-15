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
    const KEY_USER = 'user';

    const KEY_ROLE = 'role';

    /** @var  AuthenticationServiceInterface */
    protected $authService;

    /** @var  DataStoreAbstract */
    protected $userRolesDS;

    public function __construct(AuthenticationServiceInterface $authenticationService, DataStoreAbstract $userRolesDS)
    {
        $this->authService = $authenticationService;
        $this->userRolesDS = $userRolesDS;
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
        $identity = $this->authService->hasIdentity() ? $this->authService->getIdentity() : null;

        if (isset($identity)) {
            $roles = [];
            $result = $this->userRolesDS->query(new RqlQuery("eq(user_id,{$identity['id']})"));
            foreach ($result as $item) {
                $roles[] = $item[static::KEY_ROLE];
            }
            $user['roles'] = $roles;
            $request = $request->withAttribute(static::KEY_USER, $user);
        }

        if (isset($out)) {
            return $out($request,$response);
        }

        return $response;
    }
}
