<?php
/**
 * Created by PhpStorm.
 * User: victorsecuring
 * Date: 03.12.17
 * Time: 2:23 PM
 */

namespace rollun\permission\DataStore;


use rollun\datastore\DataStore\SerializedDbTable;
use rollun\datastore\TableGateway\TableManagerMysql;

class SessionTable extends SerializedDbTable
{
    const TABLE_NAME = 'session';

    const FILED_ID = 'id';
    const FIELD_MODIFIED = 'modified';
    const FILED_LIFETIME = 'lifetime';
    const FILED_DATA = 'data';


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
                    TableManagerMysql::FIELD_TYPE => "Char",
                    TableManagerMysql::PRIMARY_KEY => true,
                    TableManagerMysql::FIELD_PARAMS => [
                        'nullable' => false,
                        'length' => 32,
                    ]
                ],
                static::FIELD_MODIFIED => [
                    TableManagerMysql::FIELD_TYPE => "Integer",
                    TableManagerMysql::FIELD_PARAMS => [
                        'nullable' => true,
                    ]
                ],
                static::FILED_LIFETIME => [
                    TableManagerMysql::FIELD_TYPE => "Integer",
                    TableManagerMysql::FIELD_PARAMS => [
                        'nullable' => true,
                    ]
                ],
                static::FILED_DATA => [
                    TableManagerMysql::FIELD_TYPE => "Text",
                    TableManagerMysql::FIELD_PARAMS => [
                        'nullable' => true,
                        'length' => 65536,
                    ]
                ],
            ]
        ];
    }
}