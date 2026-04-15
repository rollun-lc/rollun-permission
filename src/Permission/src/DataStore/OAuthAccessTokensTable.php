<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\permission\DataStore;

use rollun\datastore\TableGateway\TableManagerMysql;

class OAuthAccessTokensTable extends AutoIdTable
{
    const TABLE_NAME = 'oauth_access_tokens';

    const FILED_ID = 'id';

    const FILED_REVOKED = 'revoked';

    const FILED_EXPIRES_AT = 'expires_at';

    const FILED_CLIENT_ID = 'client_id';

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
                        'length' => 80,
                    ],
                ],
                static::FILED_REVOKED => [
                    TableManagerMysql::FIELD_TYPE => "Boolean",
                    TableManagerMysql::FIELD_PARAMS => [
                        'nullable' => false,
                        'default' => false,
                    ],
                ],
                static::FILED_EXPIRES_AT => [
                    TableManagerMysql::FIELD_TYPE => "DateTime",
                    TableManagerMysql::FIELD_PARAMS => [
                        'nullable' => false,
                    ],
                ],
                static::FILED_CLIENT_ID => [
                    TableManagerMysql::FIELD_TYPE => "Varchar",
                    TableManagerMysql::FIELD_PARAMS => [
                        'nullable' => true,
                        'length' => 255,
                    ],
                ],
            ],
        ];
    }
}
