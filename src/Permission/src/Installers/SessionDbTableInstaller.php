<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 27.10.17
 * Time: 12:58
 */

namespace rollun\permission\Installers;

use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use rollun\datastore\DataStore\DataStoreException;
use rollun\datastore\DataStore\DbTable;
use rollun\datastore\DataStore\Factory\DbTableAbstractFactory;
use rollun\datastore\DataStore\Installers\DbTableInstaller;
use rollun\datastore\DataStore\Interfaces\DataStoresInterface;
use rollun\datastore\RestException;
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
use rollun\permission\DataStore\SessionTable;
use Zend\Db\TableGateway\TableGateway;
use Zend\ServiceManager\Exception\ServiceNotFoundException;

class SessionDbTableInstaller extends InstallerAbstract
{

    /**
     * install
     * @return array
     */
    public function install()
    {
        $this->createTable(SessionTable::TABLE_NAME);

        return [
            'dependencies' => [
                'abstract_factories' => [],
                'invokables' => [],
                'aliases' => [
                ],
            ],
            TableGatewayAbstractFactory::KEY_TABLE_GATEWAY => [
                SessionTable::TABLE_NAME => [],
            ],
            'dataStore' => [
                SessionTable::class => [
                    "class" => SessionTable::class,
                    DbTableAbstractFactory::KEY_TABLE_GATEWAY => SessionTable::TABLE_NAME,
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
        } catch (ServiceNotFoundException |NotFoundExceptionInterface | ContainerExceptionInterface $exception) {
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
                    SessionTable::getTableConfig()
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
                $description = "Создает структуру таблиц для хранения сессий в базе.";
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
        if (!$this->container->has("db")) {
            return false;
        }
        $config = $this->container->get("config");
        $tableManager = $this->getTableManager();
        return (
            $tableManager->hasTable(SessionTable::TABLE_NAME) &&
            isset($config['dataStore'][SessionTable::class]) &&
            isset($config[TableGatewayAbstractFactory::KEY_TABLE_GATEWAY][SessionTable::TABLE_NAME])
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