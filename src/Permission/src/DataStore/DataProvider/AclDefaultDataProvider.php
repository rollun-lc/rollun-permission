<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\permission\DataStore\DataProvider;

use rollun\permission\DataStore\AclPrivilegeTable;

class AclDefaultDataProvider
{
    /**
     * @return array
     */
    public static function getAclPrivilegeData()
    {
        return [
            [AclPrivilegeTable::FILED_NAME => "GET"],
            [AclPrivilegeTable::FILED_NAME => "POST"],
            [AclPrivilegeTable::FILED_NAME => "PUT"],
            [AclPrivilegeTable::FILED_NAME => "DELETE"],
        ];
    }
}
