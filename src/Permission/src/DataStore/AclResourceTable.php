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

class AclResourceTable extends AutoIdTable
{
    const TABLE_NAME = 'acl_resource';

    const FILED_ID = 'id';

    const FILED_NAME = 'name';

    const FILED_PATTERN = 'pattern';

    const FILED_PARENT_ID = 'parent_id';

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
                    ]
                ],
                static::FILED_NAME => [
                    TableManagerMysql::FIELD_TYPE => "Varchar",
                    TableManagerMysql::FIELD_PARAMS => [
                        'nullable' => false,
                        'length' => 255,
                    ]
                ],
                static::FILED_PATTERN => [
                    TableManagerMysql::FIELD_TYPE => "Varchar",
                    TableManagerMysql::FIELD_PARAMS => [
                        'nullable' => false,
                        'length' => 255,
                    ]
                ],
                static::FILED_PARENT_ID => [
                    TableManagerMysql::FIELD_TYPE => "Varchar",
                    TableManagerMysql::FOREIGN_KEY => [
                        'referenceTable' => static::TABLE_NAME,
                        'referenceColumn' => static::FILED_ID,
                        'onDeleteRule' => null,
                        'onUpdateRule' => null,
                        'name' => null,
                    ],
                    TableManagerMysql::FIELD_PARAMS => [
                        'nullable' => true,
                        'length' => 6,
                    ]
                ],
            ]
        ];
    }
}
