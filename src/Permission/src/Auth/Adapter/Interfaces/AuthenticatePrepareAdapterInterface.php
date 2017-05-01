<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 01.03.17
 * Time: 16:27
 */

namespace rollun\permission\Auth\Adapter\Interfaces;

use Zend\Authentication\Result;

interface AuthenticatePrepareAdapterInterface
{
    /**
     * @return Result
     */
    public function prepare();
}
