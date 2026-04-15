<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\permission\DataStore;

use rollun\datastore\TableGateway\TableManagerMysql;

class OAuthClientsTable extends AutoIdTable
{
    const TABLE_NAME = 'oauth_clients';

    const FILED_NAME = 'name';

    const FILED_REVOKED = 'revoked';

    const FILED_STATUS = 'status';

    const FILED_IS_TRUSTED = 'is_trusted';

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return static::FILED_NAME;
    }

    /**
     * @return array
     */
    public static function getTableConfig()
    {
        return [
            static::TABLE_NAME => [
                static::FILED_NAME => [
                    TableManagerMysql::FIELD_TYPE => "Varchar",
                    TableManagerMysql::PRIMARY_KEY => true,
                    TableManagerMysql::FIELD_PARAMS => [
                        'nullable' => false,
                        'length' => 255,
                    ],
                ],
                static::FILED_REVOKED => [
                    TableManagerMysql::FIELD_TYPE => "Boolean",
                    TableManagerMysql::FIELD_PARAMS => [
                        'nullable' => false,
                        'default' => false,
                    ],
                ],
                static::FILED_STATUS => [
                    TableManagerMysql::FIELD_TYPE => "Varchar",
                    TableManagerMysql::FIELD_PARAMS => [
                        'nullable' => false,
                        'length' => 32,
                    ],
                ],
                static::FILED_IS_TRUSTED => [
                    TableManagerMysql::FIELD_TYPE => "Boolean",
                    TableManagerMysql::FIELD_PARAMS => [
                        'nullable' => false,
                        'default' => false,
                    ],
                ],
            ],
        ];
    }
}
