<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 30.01.17
 * Time: 17:21
 */

namespace rollun\permission\Acl\Factory;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use rollun\datastore\DataStore\DataStoreAbstract;
use rollun\datastore\Rql\RqlQuery;
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Role\GenericRole;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\FactoryInterface;

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

    /**
     * Create an object
     *
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return object
     * @throws ServiceNotFoundException if unable to resolve the service.
     * @throws ServiceNotCreatedException if an exception is raised when
     *     creating a service.
     * @throws ContainerException if any other error occurs
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');
        if (!isset($config[static::KEY_ACL][static::KEY_DS_RULE_SERVICE]) ||
            !isset($config[static::KEY_ACL][static::KEY_DS_ROLE_SERVICE]) ||
            !isset($config[static::KEY_ACL][static::KEY_DS_PRIVILEGE_SERVICE]) ||
            !isset($config[static::KEY_ACL][static::KEY_DS_RESOURCE_SERVICE])
        ) {
            throw new ServiceNotCreatedException('Not set acl config');
        }
        if (!$container->has($config[static::KEY_ACL][static::KEY_DS_RULE_SERVICE]) ||
            !$container->has($config[static::KEY_ACL][static::KEY_DS_ROLE_SERVICE]) ||
            !$container->has($config[static::KEY_ACL][static::KEY_DS_PRIVILEGE_SERVICE]) ||
            !$container->has($config[static::KEY_ACL][static::KEY_DS_RESOURCE_SERVICE])
        ) {
            throw new ServiceNotCreatedException('Not found dataStore service');
        }

        /** @var DataStoreAbstract $dataStoreRule */
        $dataStoreRule = $container->get($config[static::KEY_ACL][static::KEY_DS_RULE_SERVICE]);
        /** @var DataStoreAbstract $dataStoreRole */
        $dataStoreRole = $container->get($config[static::KEY_ACL][static::KEY_DS_ROLE_SERVICE]);
        /** @var DataStoreAbstract $dataStorePrivilege */
        $dataStorePrivilege = $container->get($config[static::KEY_ACL][static::KEY_DS_PRIVILEGE_SERVICE]);
        /** @var DataStoreAbstract $dataStoreResource */
        $dataStoreResource = $container->get($config[static::KEY_ACL][static::KEY_DS_RESOURCE_SERVICE]);

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

    private function aclAdd(DataStoreAbstract $dataStore, Acl $acl, $addType)
    {
        $iterator = $dataStore->getIterator();
        foreach ($iterator as $role) {
            //todo: Check if exist role and resources.
            $parent = isset($role['parent_id']) ? $dataStore->read($role['parent_id'])['name'] : null;
            $acl->{"add" . $addType}($role['name'], $parent);
        }
    }

}
