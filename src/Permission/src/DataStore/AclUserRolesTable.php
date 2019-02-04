<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\permission\DataStore;

use rollun\datastore\TableGateway\TableManagerMysql;

class AclUserRolesTable extends AutoIdTable
{
    const TABLE_NAME = 'acl_user_roles';

    const FILED_ID = 'id';

    const FILED_USER_ID = 'user_id';

    const FILED_ROLES_ID = 'roles_id';

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return static::FILED_ID;
    }

    /**
     * @return array
     */
    public static function getTableConfig()
    {
        return [
            static::TABLE_NAME => [
                static::FILED_ID => [
                    TableManagerMysql::FIELD_TYPE => "Varchar",
                    TableManagerMysql::PRIMARY_KEY => true,
                    TableManagerMysql::FIELD_PARAMS => [
                        'nullable' => false,
                        'length' => 6,
                    ],
                ],
                static::FILED_USER_ID => [
                    TableManagerMysql::FIELD_TYPE => "Varchar",
                    TableManagerMysql::FOREIGN_KEY => [
                        'referenceTable' => AclUsersTable::TABLE_NAME,
                        'referenceColumn' => AclUsersTable::FILED_ID,
                        'onDeleteRule' => null,
                        'onUpdateRule' => null,
                        'name' => null,
                    ],
                    TableManagerMysql::FIELD_PARAMS => [
                        'nullable' => false,
                        'length' => 25,
                    ],
                ],
                static::FILED_ROLES_ID => [
                    TableManagerMysql::FIELD_TYPE => "Varchar",
                    TableManagerMysql::FOREIGN_KEY => [
                        'referenceTable' => AclRolesTable::TABLE_NAME,
                        'referenceColumn' => AclRolesTable::FILED_ID,
                        'onDeleteRule' => null,
                        'onUpdateRule' => null,
                        'name' => null,
                    ],
                    TableManagerMysql::FIELD_PARAMS => [
                        'nullable' => false,
                        'length' => 6,
                    ],
                ],
            ],
        ];
    }
}
