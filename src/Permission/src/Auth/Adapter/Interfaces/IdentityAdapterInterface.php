<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 01.03.17
 * Time: 16:26
 */

namespace rollun\permission\Auth\Adapter\Interfaces;

use Zend\Authentication\Result;

interface IdentityAdapterInterface
{
    /**
     * @return Result
     */
    public function identify();
}
