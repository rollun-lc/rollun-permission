<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

use rollun\datastore\DataStore\DbTable;
use rollun\datastore\DataStore\Factory\DataStoreAbstractFactory;
use rollun\datastore\DataStore\Factory\DbTableAbstractFactory;
use Zend\Db\Adapter\AdapterInterface;

return [
    'dependencies' => [
        'aliases' => [
            'db' => AdapterInterface::class,
        ],
    ],
    'db' => [
        'driver' => getenv('DB_DRIVER'),
        'database' => getenv('DB_NAME'),
        'username' => getenv('DB_USER'),
        'password' => getenv('DB_PASS'),
        'hostname' => getenv('DB_HOST'),
        'port' => getenv('DB_PORT'),
    ],
    DataStoreAbstractFactory::KEY_DATASTORE => [
        'logs' => [
            DataStoreAbstractFactory::KEY_CLASS => DbTable::class,
            DbTableAbstractFactory::KEY_TABLE_NAME => 'logs',
            DbTableAbstractFactory::KEY_DB_ADAPTER => 'db',
        ]
    ],
];
