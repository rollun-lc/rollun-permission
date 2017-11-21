<?php

/**
 * Created by PhpStorm.
 * User: root
 * Date: 27.10.17
 * Time: 14:38
 */

namespace rollun\permission\DataStore\DataProvider;

use rollun\permission\DataStore\AclPrivilegeTable;

class AclDefaultDataProvider
{
    /**
     * @return array
     */
    static public function getAclPrivilegeData()
    {
        return [
            [AclPrivilegeTable::FILED_NAME => "GET"],
            [AclPrivilegeTable::FILED_NAME => "POST"],
            [AclPrivilegeTable::FILED_NAME => "PUT"],
            [AclPrivilegeTable::FILED_NAME => "DELETE"],
        ];
    }
}
