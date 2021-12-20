<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\permission\Authorization\Factory;

use Interop\Container\ContainerInterface;
use InvalidArgumentException;
use RecursiveArrayIterator;
use rollun\datastore\DataStore\Interfaces\DataStoresInterface;
use rollun\permission\DataStore\AclDataStoreIterator;
use Xiag\Rql\Parser\Query;
use Zend\Permissions\Acl\Acl;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Create instance of Acl using 'config' service as array in ContainerInterface
 *
 * Config example:
 * <code>
 *  [
 *      'acl' => [
 *          AclFromDataStoreFactory::KEY_DATASTORE_RULE_SERVICE => 'ruleDataStoreService',
 *          AclFromDataStoreFactory::KEY_DATASTORE_ROLE_SERVICE => 'roleDataStoreService',
 *          AclFromDataStoreFactory::KEY_DATASTORE_RESOURCE_SERVICE => 'resourceDataStoreService',
 *          AclFromDataStoreFactory::KEY_DATASTORE_PRIVILEGE_SERVICE => 'privilegeDataStoreService',
 *      ],
 *  ]
 * </code>
 *
 * Class AclFromDataStoreFactory
 * @package rollun\permission\Acl\Factory
 */
class AclFromDataStoreFactory implements FactoryInterface
{
    const KEY_DATASTORE_RULE_SERVICE = 'dataStoreRuleService';

    const KEY_DATASTORE_ROLE_SERVICE = 'dataStoreRoleService';

    const KEY_DATASTORE_RESOURCE_SERVICE = 'dataStoreResourceService';

    const KEY_DATASTORE_PRIVILEGE_SERVICE = 'dataStorePrivilegeService';

    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return object|Acl
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $serviceConfig = $container->get('config')[self::class] ?? null;

        if ($serviceConfig === null) {
            throw new InvalidArgumentException('Missing config for ' . AclFromDataStoreFactory::class . ' factory');
        }

        if (!isset($serviceConfig[self::KEY_DATASTORE_RULE_SERVICE])) {
            throw new InvalidArgumentException("Missing '" . self::KEY_DATASTORE_RULE_SERVICE . "' option");
        }

        if (!isset($serviceConfig[self::KEY_DATASTORE_ROLE_SERVICE])) {
            throw new InvalidArgumentException("Missing '" . self::KEY_DATASTORE_ROLE_SERVICE . "' option");
        }

        if (!isset($serviceConfig[self::KEY_DATASTORE_PRIVILEGE_SERVICE])) {
            throw new InvalidArgumentException("Missing '" . self::KEY_DATASTORE_PRIVILEGE_SERVICE . "' option");
        }

        if (!isset($serviceConfig[self::KEY_DATASTORE_RESOURCE_SERVICE])) {
            throw new InvalidArgumentException("Missing '" . self::KEY_DATASTORE_RESOURCE_SERVICE . "' option");
        }

        $dataStoreRule = $container->get($serviceConfig[self::KEY_DATASTORE_RULE_SERVICE]);
        $dataStoreRole = $container->get($serviceConfig[self::KEY_DATASTORE_ROLE_SERVICE]);
        $dataStorePrivilege = $container->get($serviceConfig[self::KEY_DATASTORE_PRIVILEGE_SERVICE]);
        $dataStoreResource = $container->get($serviceConfig[self::KEY_DATASTORE_RESOURCE_SERVICE]);

        $query = new Query();

        $dataStoreRuleList = $dataStoreRule->query($query);

        $dataStoreRoleList = $dataStoreRole->query($query);
        $roleList = array_combine(array_column($dataStoreRoleList, 'id'), $dataStoreRoleList);

        $dataStoreResourceList = $dataStoreResource->query($query);
        $resourceList = array_combine(array_column($dataStoreResourceList, 'id'), $dataStoreResourceList);

        $acl = new Acl();
        $this->aclAdd($roleList, $acl, "Role");
        $this->aclAdd($resourceList, $acl, "Resource");

        $dataStorePrivilegeList = $dataStorePrivilege->query($query);
        $privilegeList = array_combine(array_column($dataStorePrivilegeList, 'id'), $dataStorePrivilegeList);

        foreach ($dataStoreRuleList as $item) {
            if ($item['allow_flag']) {
                $acl->allow(
                    $roleList[$item['role_id']]['name'],
                    $resourceList[$item['resource_id']]['name'],
                    $privilegeList[$item['privilege_id']]['name']
                );
            } else {
                $acl->deny(
                    $roleList[$item['role_id']]['name'],
                    $resourceList[$item['resource_id']]['name'],
                    $privilegeList[$item['privilege_id']]['name']
                );
            }
        }

        return $acl;
    }

    /**
     * @param array $data
     * @param Acl $acl
     * @param $addType
     */
    private function aclAdd(array $data, Acl $acl, $addType)
    {
        $iterator = new AclDataStoreIterator($data);

        foreach ($iterator as $record) {
            $parent = isset($record['parent_id']) ? $data[$record['parent_id']]['name'] : null;
            $acl->{"add" . $addType}($record['name'], $parent);
        }
    }
}

