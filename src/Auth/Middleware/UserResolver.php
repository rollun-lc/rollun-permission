<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 15.02.17
 * Time: 15:04
 */

namespace rollun\permission\Auth\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use rollun\datastore\DataStore\DataStoreAbstract;
use rollun\datastore\Rql\RqlQuery;
use Zend\Stratigility\MiddlewareInterface;

class UserResolver implements MiddlewareInterface
{

    const KEY_USER = 'user';

    const KEY_ROLE = 'role';

    /** @var  DataStoreAbstract */
    protected $userDS;

    /** @var  DataStoreAbstract */
    protected $userRolesDS;


    /**
     * UserResolver constructor.
     * @param DataStoreAbstract $userDS
     * @param DataStoreAbstract $userRolesDS
     */
    public function __construct(DataStoreAbstract $userDS, DataStoreAbstract $userRolesDS)
    {
        $this->userDS = $userDS;
        $this->userRolesDS = $userRolesDS;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param null|callable $out
     * @return null|Response
     */
    public function __invoke(Request $request, Response $response, callable $out = null)
    {
        $identity = $request->getAttribute(IdentifyAction::KEY_IDENTITY);
        $user = $this->getUser($identity);

        $request = $request->withAttribute(static::KEY_USER, $user);

        if (isset($out)) {
            return $out($request,$response);
        }

        return $response;
    }

    /**
     * return user array (with roles)
     * @param string $userId
     * @return AuthenticationAction
     */
    protected function getUser($userId)
    {
        $user = $this->userDS->read($userId);
        if (isset($user)) {
            $user['roles'] = $this->getRoles($userId);
        }
        return $user;
    }

    /**
     * return array with all user roles
     * @param string $userId
     * @return array
     */
    protected function getRoles($userId)
    {
        $roles = [];
        $result = $this->userRolesDS->query(new RqlQuery("eq(user_id, $userId)"));
        foreach ($result as $item) {
            $roles[] = $item[static::KEY_ROLE];
        }
        return $roles;
    }
}
