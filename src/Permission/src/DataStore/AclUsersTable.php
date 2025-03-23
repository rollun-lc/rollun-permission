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

    public function readByName($id)
    {
        $this->checkIdentifierType($id);
        $identifier = $this->getName();

        $request = [$identifier => $id];

        $logContext = [
            self::LOG_METHOD => __FUNCTION__,
            self::LOG_TABLE => $this->dbTable->getTable(),
            self::LOG_REQUEST => $request,
        ];

        try {
            $start = microtime(true);
            $rowSet = $this->dbTable->select($request);
            $end = microtime(true);
        } catch (\Throwable $e) {
            $logContext['exception'] = $e;
            $this->writeLogsIfNeeded($logContext, "Request to db table '{$this->dbTable->getTable()}' failed");
            throw $e;
        }

        $row = $rowSet->current();
        $response = null;

        if (isset($row)) {
            $response = $row->getArrayCopy();
        }

        $logContext[self::LOG_TIME] = $this->getRequestTime($start, $end);
        $logContext[self::LOG_RESPONSE] = $response;

        $this->writeLogsIfNeeded($logContext);

        return $response;
    }

    public function getName()
    {
        return self::FILED_NAME;
    }

    private function getRequestTime(float $start, float $end): float
    {
        return round($end - $start, 3);
    }
}
