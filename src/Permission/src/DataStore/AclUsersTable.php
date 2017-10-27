<?php

/**
 * Created by PhpStorm.
 * User: root
 * Date: 26.10.17
 * Time: 19:10
 */

namespace rollun\permission\DataStore;

use rollun\datastore\DataStore\SerializedDbTable;
use rollun\datastore\TableGateway\TableManagerMysql;

class AclUsersTable extends AutoIdTable
{
    const TABLE_NAME = 'acl_users';

    const FILED_ID = 'id';

    const FILED_NAME = 'name';

    const FILED_PASSWORD = 'password';

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
                        'length' => 25,
                    ]
                ],
                static::FILED_NAME => [
                    TableManagerMysql::FIELD_TYPE => "Varchar",
                    TableManagerMysql::FIELD_PARAMS => [
                        'nullable' => false,
                        'length' => 255,
                    ]
                ],
                static::FILED_PASSWORD => [
                    TableManagerMysql::FIELD_TYPE => "Varchar",
                    TableManagerMysql::FIELD_PARAMS => [
                        'nullable' => false,
                        'length' => 255,
                    ]
                ],
            ]
        ];
    }
}
