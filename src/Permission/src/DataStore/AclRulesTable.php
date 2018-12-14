<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\permission\DataStore;

use rollun\datastore\TableGateway\TableManagerMysql;

class AclRulesTable extends AutoIdTable
{
    const TABLE_NAME = 'acl_rules';

    const FILED_ID = 'id';

    const FILED_ROLE_ID = 'role_id';

    const FILED_RESOURCE_ID = 'resource_id';

    const FILED_PRIVILEGE_ID = 'privilege_id';

    const FILED_ALLOW_FLAG = 'allow_flag';

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
                static::FILED_ROLE_ID => [
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
                static::FILED_RESOURCE_ID => [
                    TableManagerMysql::FIELD_TYPE => "Varchar",
                    TableManagerMysql::FOREIGN_KEY => [
                        'referenceTable' => AclResourceTable::TABLE_NAME,
                        'referenceColumn' => AclResourceTable::FILED_ID,
                        'onDeleteRule' => null,
                        'onUpdateRule' => null,
                        'name' => null,
                    ],
                    TableManagerMysql::FIELD_PARAMS => [
                        'nullable' => false,
                        'length' => 6,
                    ],
                ],
                static::FILED_PRIVILEGE_ID => [
                    TableManagerMysql::FIELD_TYPE => "Varchar",
                    TableManagerMysql::FOREIGN_KEY => [
                        'referenceTable' => AclPrivilegeTable::TABLE_NAME,
                        'referenceColumn' => AclPrivilegeTable::FILED_ID,
                        'onDeleteRule' => null,
                        'onUpdateRule' => null,
                        'name' => null,
                    ],
                    TableManagerMysql::FIELD_PARAMS => [
                        'nullable' => false,
                        'length' => 6,
                    ],
                ],
                static::FILED_ALLOW_FLAG => [
                    TableManagerMysql::FIELD_TYPE => "Boolean",
                    TableManagerMysql::FIELD_PARAMS => [
                        'nullable' => false,
                        'default' => true,
                    ],
                ],
            ],
        ];
    }
}
