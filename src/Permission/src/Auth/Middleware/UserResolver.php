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
use rollun\datastore\DataStore\Interfaces\DataStoresInterface;
use rollun\datastore\Rql\RqlQuery;
use rollun\permission\DataStore\AclRolesTable;
use rollun\permission\DataStore\AclUserRolesTable;
use Zend\Stratigility\MiddlewareInterface;

class UserResolver implements MiddlewareInterface
{

    const KEY_ATTRIBUTE_USER = 'user';

    const KEY_ROLE_ID = AclUserRolesTable::FILED_ROLES_ID;

    const KEY_ROLE_NAME = AclRolesTable::FILED_NAME;

    /** @var  DataStoreAbstract */
    protected $userDS;

    /** @var  DataStoreAbstract */
    protected $userRolesDS;
    /**
     * @var DataStoreAbstract
     */
    private $rolesDS;

    /**
     * UserResolver constructor.
     * @param DataStoreAbstract|DataStoresInterface $userDS
     * @param DataStoreAbstract|DataStoresInterface $rolesDS
     * @param DataStoreAbstract|DataStoresInterface $userRolesDS
     */
    public function __construct(DataStoresInterface $userDS, DataStoresInterface $rolesDS, DataStoresInterface $userRolesDS)
    {
        $this->userDS = $userDS;
        $this->userRolesDS = $userRolesDS;
        $this->rolesDS = $rolesDS;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param null|callable $out
     * @return null|Response
     */
    public function __invoke(Request $request, Response $response, callable $out = null)
    {
        $identity = $request->getAttribute(IdentityAction::KEY_ATTRIBUTE_IDENTITY);
        $user = $this->getUser($identity);

        $request = $request->withAttribute(static::KEY_ATTRIBUTE_USER, $user);

        if (isset($out)) {
            return $out($request,$response);
        }

        return $response;
    }

    /**
     * return user array (with roles)
     * @param string $userId
     * @return LazyAuthenticationAction
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
        $result = $this->userRolesDS->query(new RqlQuery("eq(user_id,string:$userId)"));
        foreach ($result as $item) {
            $role = $this->rolesDS->read($item[static::KEY_ROLE_ID]);
            if (isset($role[static::KEY_ROLE_NAME])) {
                $roles[] = $role[static::KEY_ROLE_NAME];
            }
        }
        return $roles;
    }
}
