<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 01.02.17
 * Time: 12:11
 */

namespace rollun\permission\DataStore;

use rollun\datastore\DataStore\Memory;

class MemoryConfig extends Memory
{
    public function __construct($items = [])
    {
        foreach ($items as $key => $item) {
            if (isset($item['id'])) {
                $this->items[$item['id']] = $item;
            } else {
                $this->items[$key] = $item;
            }
        }
        parent::__construct();
    }
}
