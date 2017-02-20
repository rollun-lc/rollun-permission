<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 20.02.17
 * Time: 14:41
 */

namespace rollun\permission\Auth\Adapter;

interface LogOutInterface
{
    /**
     * Erase user data.
     * @return void
     */
    public function logout();
}
