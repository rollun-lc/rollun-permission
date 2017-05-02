<?php
/**
 * Created by PhpStorm.
 * User: victorsecuring
 * Date: 01.05.17
 * Time: 5:12 PM
 */

namespace rollun\permission\Auth\Adapter\Interfaces;

use Zend\Authentication\Result;

interface RegisterAdapterInterface
{
    /**
     * @return Result
     */
    public function register();
}