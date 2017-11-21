<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 27.10.17
 * Time: 12:58
 */

namespace rollun\permission\Installers;

use Exception;
use rollun\datastore\DataStore\DbTable;
use rollun\datastore\DataStore\Factory\DbTableAbstractFactory;
use rollun\datastore\DataStore\Installers\DbTableInstaller;
use rollun\datastore\DataStore\Interfaces\DataStoresInterface;
use rollun\datastore\TableGateway\DbSql\MultiInsertSql;
use rollun\datastore\TableGateway\Factory\TableGatewayAbstractFactory;
use rollun\datastore\TableGateway\TableManagerMysql;
use rollun\installer\Install\InstallerAbstract;
use rollun\permission\DataStore\AclPrivilegeTable;
use rollun\permission\DataStore\AclResourceTable;
use rollun\permission\DataStore\AclRolesTable;
use rollun\permission\DataStore\AclRulesTable;
use rollun\permission\DataStore\AclUserRolesTable;
use rollun\permission\DataStore\AclUsersTable;
use rollun\permission\DataStore\DataProvider\AclDefaultDataProvider;
use Zend\Db\TableGateway\TableGateway;
use Zend\ServiceManager\Exception\ServiceNotFoundException;

class AclDbTableInstaller extends InstallerAbstract
{

    /**
     * install
     * @return array
     */
    public function install()
    {
        $this->createTable(AclUsersTable::TABLE_NAME);
        $this->createTable(AclRolesTable::TABLE_NAME);
        $this->createTable(AclPrivilegeTable::TABLE_NAME);
        $this->createTable(AclResourceTable::TABLE_NAME);
        $this->createTable(AclUserRolesTable::TABLE_NAME);
        $this->createTable(AclRulesTable::TABLE_NAME);

        if ($this->consoleIO->askConfirmation(
            "Do you want to fill in the configuration with the basic settings")) {
            $this->addData([
                AclPrivilegeTable::TABLE_NAME => AclDefaultDataProvider::getAclPrivilegeData()
            ], AclPrivilegeTable::class);
        }
        return [
            'dependencies' => [
                'abstract_factories' => [],
                'invokables' => [],
                'factories' => [
                ],
            ],
            TableGatewayAbstractFactory::KEY_TABLE_GATEWAY => [
                AclUsersTable::TABLE_NAME => [],
                AclRolesTable::TABLE_NAME => [],
                AclPrivilegeTable::TABLE_NAME => [],
                AclResourceTable::TABLE_NAME => [],
                AclUserRolesTable::TABLE_NAME => [],
                AclRulesTable::TABLE_NAME => [],
            ],
            'dataStore' => [
                AclUsersTable::class => [
                    "class" => AclUsersTable::class,
                    DbTableAbstractFactory::KEY_TABLE_GATEWAY => AclUsersTable::TABLE_NAME,
                ],
                AclRolesTable::class => [
                    "class" => AclRolesTable::class,
                    DbTableAbstractFactory::KEY_TABLE_GATEWAY => AclRolesTable::TABLE_NAME,
                ],
                AclPrivilegeTable::class => [
                    "class" => AclPrivilegeTable::class,
                    DbTableAbstractFactory::KEY_TABLE_GATEWAY => AclPrivilegeTable::TABLE_NAME,
                ],
                AclResourceTable::class => [
                    "class" => AclResourceTable::class,
                    DbTableAbstractFactory::KEY_TABLE_GATEWAY => AclResourceTable::TABLE_NAME,
                ],
                AclUserRolesTable::class => [
                    "class" => AclUserRolesTable::class,
                    DbTableAbstractFactory::KEY_TABLE_GATEWAY => AclUserRolesTable::TABLE_NAME,
                ],
                AclRulesTable::class => [
                    "class" => AclRulesTable::class,
                    DbTableAbstractFactory::KEY_TABLE_GATEWAY => AclRulesTable::TABLE_NAME,
                ],
            ]
        ];
    }

    /**
     * @param $tableName
     */
    protected function createTable($tableName)
    {
        $tableManager = $this->getTableManager();
        if (!$tableManager->hasTable($tableName)) {
            $tableManager->createTable($tableName);
            $this->consoleIO->write("Created $tableName");
        }
    }

    protected function getDb()
    {
        try {
            $dbAdapter = $this->container->get('db');
        } catch (ServiceNotFoundException $exception) {
            $dbAdapter = null;
        }
        return $dbAdapter;
    }


    /**
     * @return TableManagerMysql
     */
    protected function getTableManager()
    {
        static $tableManager;
        if (!isset($tableManager)) {
            $dbAdapter = $this->getDb();
            if (is_null($dbAdapter)) {
                return null;
            }
            $tableConfig = [
                TableManagerMysql::KEY_TABLES_CONFIGS => array_merge(
                    AclUsersTable::getTableConfig(),
                    AclRolesTable::getTableConfig(),
                    AclPrivilegeTable::getTableConfig(),
                    AclResourceTable::getTableConfig(),
                    AclUserRolesTable::getTableConfig(),
                    AclRulesTable::getTableConfig()
                )
            ];
            $tableManager = new TableManagerMysql($dbAdapter, $tableConfig);
        }
        return $tableManager;
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

    /**
     * @return bool
     */
    public function isInstall()
    {
        if(!$this->container->has("db")) {
            return false;
        }
        $config = $this->container->has("config");
        $tableManager = $this->getTableManager();
        return (
            $tableManager->hasTable(AclUsersTable::TABLE_NAME) &&
            isset($config['dataStore'][AclUsersTable::class]) &&
            isset($config[TableGatewayAbstractFactory::KEY_TABLE_GATEWAY][AclUsersTable::TABLE_NAME]) &&
            $tableManager->hasTable(AclRolesTable::TABLE_NAME) &&
            isset($config['dataStore'][AclRolesTable::class]) &&
            isset($config[TableGatewayAbstractFactory::KEY_TABLE_GATEWAY][AclRolesTable::TABLE_NAME]) &&
            $tableManager->hasTable(AclPrivilegeTable::TABLE_NAME) &&
            isset($config['dataStore'][AclPrivilegeTable::class]) &&
            isset($config[TableGatewayAbstractFactory::KEY_TABLE_GATEWAY][AclPrivilegeTable::TABLE_NAME]) &&
            $tableManager->hasTable(AclResourceTable::TABLE_NAME) &&
            isset($config['dataStore'][AclResourceTable::class]) &&
            isset($config[TableGatewayAbstractFactory::KEY_TABLE_GATEWAY][AclResourceTable::TABLE_NAME]) &&
            $tableManager->hasTable(AclUserRolesTable::TABLE_NAME) &&
            isset($config['dataStore'][AclUserRolesTable::class]) &&
            isset($config[TableGatewayAbstractFactory::KEY_TABLE_GATEWAY][AclUserRolesTable::TABLE_NAME]) &&
            $tableManager->hasTable(AclRulesTable::TABLE_NAME) &&
            isset($config['dataStore'][AclRulesTable::class]) &&
            isset($config[TableGatewayAbstractFactory::KEY_TABLE_GATEWAY][AclRulesTable::TABLE_NAME])
        );
    }

    public function getDependencyInstallers()
    {
        return [
            DbTableInstaller::class,
            ACLInstaller::class
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
        foreach ($data as $key => $value) {
            $sql = new MultiInsertSql($dbAdapter, $key);
            $tableGateway = new TableGateway($key, $dbAdapter, null, null, $sql);
            /** @var DataStoresInterface $dataStore */
            $dataStore = new $dbTableClass($tableGateway);
            echo "create $key" . PHP_EOL;
            $dataStore->create($value, true);
        }
    }
}