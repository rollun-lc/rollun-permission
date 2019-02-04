<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\permission;

use Exception;
use rollun\datastore\DataStore\DbTable;
use rollun\datastore\DataStore\Interfaces\DataStoresInterface;
use rollun\datastore\TableGateway\DbSql\MultiInsertSql;
use rollun\datastore\TableGateway\TableManagerMysql;
use rollun\installer\Install\InstallerAbstract;
use rollun\permission\DataStore\AclPrivilegeTable;
use rollun\permission\DataStore\AclResourceTable;
use rollun\permission\DataStore\AclRolesTable;
use rollun\permission\DataStore\AclRulesTable;
use rollun\permission\DataStore\AclUserRolesTable;
use rollun\permission\DataStore\AclUsersTable;
use rollun\permission\DataStore\DataProvider\AclDefaultDataProvider;
use rollun\utils\DbInstaller;
use Zend\Db\TableGateway\TableGateway;

class AssetInstaller extends InstallerAbstract
{
    const USER_DATASTORE_SERVICE = 'userDataStore';
    const ROLE_DATASTORE_SERVICE = 'roleDataStore';
    const USER_ROLE_DATASTORE_SERVICE = 'userRoleDataStore';
    const RULE_DATASTORE_SERVICE = 'ruleDataStore';
    const RESOURCE_DATASTORE_SERVICE = 'resourceDataStore';
    const PRIVILEGE_DATASTORE_SERVICE = 'privilegeDataStore';

    /**
     * @var TableManagerMysql
     */
    protected $tableManager;

    protected $tableToCreate = [
        AclUsersTable::TABLE_NAME,
        AclRolesTable::TABLE_NAME,
        AclPrivilegeTable::TABLE_NAME,
        AclResourceTable::TABLE_NAME,
        AclUserRolesTable::TABLE_NAME,
        AclRulesTable::TABLE_NAME,
    ];

    public function install()
    {
        try {
            foreach ($this->tableToCreate as $tableName) {
                $this->createTable($tableName);
            }

            if ($this->consoleIO->askConfirmation(
                "Do you want to fill in the configuration with the basic settings (y/n)?"
            )) {
                $this->addData(
                    [
                        AclPrivilegeTable::TABLE_NAME => AclDefaultDataProvider::getAclPrivilegeData(),
                    ],
                    AclPrivilegeTable::class
                );
            }
        } catch (\Throwable $t) {
            $this->consoleIO->write($t->getMessage());
            exit($t->getCode());
        }
    }

    /**
     * @param $tableName
     * @throws Exception
     */
    protected function createTable($tableName)
    {
        try {
            $tableManager = $this->getTableManager();

            if (is_null($tableManager)) {
                throw new Exception("Can't create '" . TableManagerMysql::class . "'");
            }

            if (!$tableManager->hasTable($tableName)) {
                $tableManager->createTable($tableName);
                $this->consoleIO->write("Table '$tableName' was successfully created");
            }
        } catch (\Throwable $t) {
            throw new Exception("Can't create table '$tableName'. Reason: {$t->getMessage()}", 0, $t);
        }
    }

    protected function getDb()
    {
        if ($this->container->has('db')) {
            return $this->container->get('db');
        }

        return null;
    }

    /**
     * @return TableManagerMysql
     * @throws Exception
     */
    protected function getTableManager()
    {
        $db = $this->getDb();

        if (is_null($db)) {
            return null;
        }

        if (is_null($this->tableManager)) {
            $this->tableManager = new TableManagerMysql(
                $db,
                [
                    TableManagerMysql::KEY_TABLES_CONFIGS => array_merge(
                        AclUsersTable::getTableConfig(),
                        AclRolesTable::getTableConfig(),
                        AclPrivilegeTable::getTableConfig(),
                        AclResourceTable::getTableConfig(),
                        AclUserRolesTable::getTableConfig(),
                        AclRulesTable::getTableConfig()
                    ),
                ]
            );
        }

        return $this->tableManager;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function isInstall()
    {
        $tableManager = $this->getTableManager();

        if (is_null($tableManager)) {
            return false;
        }

        foreach ($this->tableToCreate as $tableName) {
            if (!$tableManager->hasTable($tableName)) {
                return false;
            }
        }

        return true;
    }

    public function getDependencyInstallers()
    {
        return [
            DbInstaller::class,
        ];
    }

    /**
     * @param array $data
     * @param $dbTableClass
     * @throws Exception
     */
    protected function addData(array $data, $dbTableClass)
    {
        $dbAdapter = $this->container->get('db');

        if (!is_a($dbTableClass, DbTable::class, true)) {
            throw new Exception("$dbTableClass not instance of " . DbTable::class);
        }

        foreach ($data as $tableName => $records) {
            $sql = new MultiInsertSql($dbAdapter, $tableName);
            $tableGateway = new TableGateway($tableName, $dbAdapter, null, null, $sql);

            /** @var DataStoresInterface $dataStore */
            $dataStore = new $dbTableClass($tableGateway);

            echo "Insert default values for table '$tableName': " . json_encode($records);
            $dataStore->create($records, true);
        }
    }

    /**
     * Clean all installation
     * @return void
     */
    public function uninstall()
    {
        // TODO: Implement uninstall() method.
    }

    /**
     * Return string with description of installable functional.
     * @param string $lang ; set select language for description getted.
     * @return string
     */
    public function getDescription($lang = "en")
    {
        switch ($lang) {
            case "ru":
                $description = "Создает структуру таблиц в базе, и наполняет их базовыми данными если нужно.";
                break;
            default:
                $description = "Does not exist.";
        }

        return $description;
    }
}
