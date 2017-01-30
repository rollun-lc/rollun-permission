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

class AclDataStoreFactory implements FactoryInterface
{

    const KEY_DS_RULE_SERVICE = 'dataStoreRuleService';
    const KEY_DS_ROLE_SERVICE = 'dataStoreRoleService';
    const KEY_DS_RESOURCE_SERVICE = 'dataStoreResourceService';

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
        if (!isset($config['acl'][static::KEY_DS_RULE_SERVICE]) ||
            !isset($config['acl'][static::KEY_DS_ROLE_SERVICE]) ||
            !isset($config['acl'][static::KEY_DS_RESOURCE_SERVICE])
        ) {
            throw new ServiceNotCreatedException('Not set acl config');
        }
        if (!$container->has(static::KEY_DS_RULE_SERVICE) ||
            !$container->has(static::KEY_DS_ROLE_SERVICE) ||
            !$container->has(static::KEY_DS_RESOURCE_SERVICE)
        ) {
            throw new ServiceNotCreatedException('Not found dataStore service');
        }

        /** @var DataStoreAbstract $dataStoreRule */
        $dataStoreRule = $container->get(static::KEY_DS_RULE_SERVICE);
        /** @var DataStoreAbstract $dataStoreRole */
        $dataStoreRole = $container->get(static::KEY_DS_ROLE_SERVICE);
        /** @var DataStoreAbstract $dataStoreResource */
        $dataStoreResource = $container->get(static::KEY_DS_RESOURCE_SERVICE);

        $acl = new Acl();

        $this->aclAdd($dataStoreRole, $acl, "Role");
        $this->aclAdd($dataStoreResource, $acl, "Resource");

        foreach ($dataStoreRule as $item) {
            $acl->allow($item['role'], $item['resource'], $item['privileges']);
        }

        return $acl;
    }

    private function aclAdd(DataStoreAbstract $dataStore, Acl $acl, $addType)
    {
        foreach ($dataStore as $role)
        {
            $parentId = $role['parent_id'];
            $parent = $dataStore->query(new RqlQuery("eq(id,$parentId)"));
            $acl->${"add" . $addType}($role['name'], $parent);
        }
    }

}
