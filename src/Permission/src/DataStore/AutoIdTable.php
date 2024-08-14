<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\permission\DataStore;

use rollun\datastore\DataStore\DataStoreException;
use rollun\datastore\DataStore\SerializedDbTable;
use rollun\datastore\DataStore\Traits\AutoIdGeneratorTrait;
use rollun\utils\IdGenerator;
use Laminas\Db\TableGateway\TableGateway;

class AutoIdTable extends SerializedDbTable
{
    use AutoIdGeneratorTrait;

    /**
     * AutoIdTable constructor.
     * @param TableGateway|null $dbTable
     */
    public function __construct(TableGateway $dbTable = null)
    {
        $this->idGenerator = new IdGenerator(5, "QWERTYUIOPADFHKLZXVBNM123456789");
        parent::__construct($dbTable);
    }

    /**
     * @param array $itemData
     * @throws DataStoreException
     */
    protected function setItemId(array &$itemData)
    {
        if (!isset($itemData[$this->getIdentifier()])) {
            $itemData[$this->getIdentifier()] = $this->generateId();
        }
    }

    /**
     * @param array $itemData
     * @return int
     */
    protected function isDataMultipleData(array $itemData)
    {
        return (count($itemData, COUNT_RECURSIVE) - count($itemData));
    }

    /**
     * @param $itemData
     * @param bool $rewriteIfExist
     * @return array|mixed|null
     * @throws DataStoreException
     */
    public function create($itemData, $rewriteIfExist = false)
    {
        if ($this->isDataMultipleData($itemData)) {
            foreach ($itemData as &$datum) {
                $this->setItemId($datum);
            }
        } else {
            $this->setItemId($itemData);
        }

        return parent::create($itemData, $rewriteIfExist);
    }

    /**
     * @param $itemData
     * @param bool $createIfAbsent
     * @return array|mixed|null
     * @throws DataStoreException
     */
    public function update($itemData, $createIfAbsent = false)
    {
        if ($this->isDataMultipleData($itemData)) {
            foreach ($itemData as &$datum) {
                $this->setItemId($datum);
            }
        } else {
            $this->setItemId($itemData);
        }

        return parent::update($itemData, $createIfAbsent);
    }
}
