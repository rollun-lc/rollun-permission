<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace rollun\permission\Authentication;

use rollun\datastore\DataStore\Interfaces\DataStoresInterface;
use rollun\permission\DataStore\AclRolesTable;
use rollun\permission\DataStore\AclUserRolesTable;
use Xiag\Rql\Parser\Node\Query\ScalarOperator\EqNode;
use Xiag\Rql\Parser\Query;

class UserRolesResolver
{
    public const DEFAULT_USER_ID_FIELD = AclUserRolesTable::FILED_USER_ID;
    public const DEFAULT_ROLE_ID_FIELD = AclUserRolesTable::FILED_ROLES_ID;
    public const DEFAULT_ROLE_NAME_FIELD = AclRolesTable::FILED_NAME;

    private DataStoresInterface $userRoles;
    private DataStoresInterface $roles;
    private string $userIdField;
    private string $roleIdField;
    private string $roleNameField;

    public function __construct(DataStoresInterface $userRoles, DataStoresInterface $roles, array $config = [])
    {
        $this->userRoles = $userRoles;
        $this->roles = $roles;
        $this->userIdField = $config['userIdInUserRoles'] ?? self::DEFAULT_USER_ID_FIELD;
        $this->roleIdField = $config['roleIdInUserRoles'] ?? self::DEFAULT_ROLE_ID_FIELD;
        $this->roleNameField = $config['roleName'] ?? self::DEFAULT_ROLE_NAME_FIELD;
    }

    /**
     * @return string[]
     */
    public function getRolesByUserId(string $userId): array
    {
        $roles = [];
        $query = new Query();
        $query->setQuery(new EqNode($this->userIdField, $userId));

        $userRoles = $this->userRoles->query($query);
        foreach ($userRoles as $userRole) {
            if (!isset($userRole[$this->roleIdField])) {
                continue;
            }

            $role = $this->roles->read($userRole[$this->roleIdField]);
            if (isset($role[$this->roleNameField])) {
                $roles[] = (string)$role[$this->roleNameField];
            }
        }

        return $roles;
    }
}

