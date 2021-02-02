<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\permission\DataStore;

use rollun\datastore\DataStore\DataStoreException;
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
                    ],
                ],
                static::FILED_NAME => [
                    TableManagerMysql::FIELD_TYPE => "Varchar",
                    TableManagerMysql::FIELD_PARAMS => [
                        'nullable' => false,
                        'length' => 255,
                    ],
                ],
                static::FILED_PASSWORD => [
                    TableManagerMysql::FIELD_TYPE => "Varchar",
                    TableManagerMysql::FIELD_PARAMS => [
                        'nullable' => false,
                        'length' => 255,
                    ],
                ],
            ],
        ];
    }

    /**
     * @param $itemData
     * @param bool $rewriteIfExist
     * @return array|mixed|null
     * @throws DataStoreException
     */
    public function create($itemData, $rewriteIfExist = false)
    {
        if ($this->isDataMultipleData($itemData)) {
            foreach ($itemData as &$datum) {
                $this->passwordHash($datum);
            }
        } else {
            $this->passwordHash($itemData);
        }

        return parent::create($itemData, $rewriteIfExist);
    }

    /**
     * @param $itemData
     * @param bool $createIfAbsent
     * @return array|mixed|null
     * @throws DataStoreException
     */
    public function update($itemData, $createIfAbsent = false)
    {
        if ($this->isDataMultipleData($itemData)) {
            foreach ($itemData as &$datum) {
                $this->passwordHash($datum);
            }
        } else {
            $this->passwordHash($itemData);
        }

        return parent::update($itemData, $createIfAbsent);
    }

    /**
     * Create password hash using bcrypt algorithm.
     *
     * @param array $itemData
     */
    private function passwordHash(array &$itemData)
    {
        if (isset($itemData[self::FILED_PASSWORD])) {
            $hashedPassword = password_hash($itemData[self::FILED_PASSWORD], PASSWORD_BCRYPT);
            $itemData[self::FILED_PASSWORD] = $hashedPassword;
        }
    }
}
