<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\permission\Acl\Factory;

use Interop\Container\ContainerInterface;
use rollun\datastore\DataStore\Interfaces\DataStoresInterface;
use Zend\Permissions\Acl\Acl;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Create instance of Acl using 'config' service as array in ContainerInterface
 *
 * Config example:
 * <code>
 *  [
 *      'acl' => [
 *          AclFromDataStoreFactory::KEY_DS_RULE_SERVICE => 'ruleDataStoreService',
 *          AclFromDataStoreFactory::KEY_DS_ROLE_SERVICE => 'roleDataStoreService',
 *          AclFromDataStoreFactory::KEY_DS_RESOURCE_SERVICE => 'resourceDataStoreService',
 *          AclFromDataStoreFactory::KEY_DS_PRIVILEGE_SERVICE => 'privilegeDataStoreService',
 *      ],
 *  ]
 * </code>
 *
 * Class AclFromDataStoreFactory
 * @package rollun\permission\Acl\Factory
 */
class AclFromDataStoreFactory implements FactoryInterface
{
    const KEY_ACL = 'acl';

    const KEY_DS_RULE_SERVICE = 'dataStoreRuleService';

    const KEY_DS_ROLE_SERVICE = 'dataStoreRoleService';

    const KEY_DS_RESOURCE_SERVICE = 'dataStoreResourceService';

    const KEY_DS_PRIVILEGE_SERVICE = 'dataStorePrivilegeService';

    const KEY_DS_ID = 'id';

    const KEY_DS_ROLE = 'role';

    const KEY_DS_RESOURCE = 'resource_id';

    const KEY_DS_PRIVILEGE = 'privileges_id';

    const DEFAULT_RULES_DS = 'rulesDS';

    const DEFAULT_ROLES_DS = 'rolesDS';

    const DEFAULT_RESOURCE_DS = 'resourceDS';

    const DEFAULT_PRIVILEGE_DS = 'privilegeDS';

    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return object|Acl
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');

        $rulesDS = isset($config[static::KEY_ACL][static::KEY_DS_RULE_SERVICE]) ?? static::DEFAULT_RULES_DS;
        $rolesDS = isset($config[static::KEY_ACL][static::KEY_DS_ROLE_SERVICE]) ?? static::DEFAULT_ROLES_DS;
        $resourceDS = isset($config[static::KEY_ACL][static::KEY_DS_PRIVILEGE_SERVICE]) ?? static::DEFAULT_RESOURCE_DS;
        $privilegeDS = isset($config[static::KEY_ACL][static::KEY_DS_RESOURCE_SERVICE]) ?? static::DEFAULT_PRIVILEGE_DS;

        $dataStoreRule = $container->get($rulesDS);
        $dataStoreRole = $container->get($rolesDS);
        $dataStorePrivilege = $container->get($resourceDS);
        $dataStoreResource = $container->get($privilegeDS);

        $acl = new Acl();
        $this->aclAdd($dataStoreRole, $acl, "Role");
        $this->aclAdd($dataStoreResource, $acl, "Resource");

        foreach ($dataStoreRule as $item) {
            $role = $dataStoreRole->read($item['role_id']);
            $resource = $dataStoreResource->read($item['resource_id']);
            $privilege = $dataStorePrivilege->read($item['privilege_id']);

            if ($item['allow_flag']) {
                $acl->allow($role['name'], $resource['name'], $privilege['name']);
            } else {
                $acl->deny($role['name'], $resource['name'], $privilege['name']);
            }
        }

        return $acl;
    }

    /**
     * @param DataStoresInterface $dataStore
     * @param Acl $acl
     * @param $addType
     */
    private function aclAdd(DataStoresInterface $dataStore, Acl $acl, $addType)
    {
        $iterator = $dataStore->getIterator();

        foreach ($iterator as $record) {
            $parent = isset($record['parent_id']) ? $dataStore->read($record['parent_id'])['name'] : null;
            $acl->{"add" . $addType}($record['name'], $parent);
        }
    }
}
