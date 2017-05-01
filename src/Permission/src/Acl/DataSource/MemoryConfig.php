<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 01.02.17
 * Time: 12:11
 */

namespace rollun\permission\Acl\DataSource;

use rollun\datastore\DataStore\Interfaces\DataSourceInterface;
use rollun\datastore\DataStore\Memory;

class MemoryConfig implements DataSourceInterface
{
    /** @var  array */
    protected $items;

    /**
     * MemoryConfig constructor.
     * @param array $items
     */
    public function __construct($items = [])
    {
        foreach ($items as $key => $item) {
            if (isset($item['id'])) {
                $this->items[$item['id']] = $item;
            } else {
                $this->items[$key] = $item;
            }
        }
    }

    /**
     * @return array Return data of DataSource
     */
    public function getAll()
    {
        return $this->items;
    }
}
