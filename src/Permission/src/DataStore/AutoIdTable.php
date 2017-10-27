<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 27.10.17
 * Time: 11:40
 */

namespace rollun\permission\DataStore;

use rollun\datastore\DataStore\SerializedDbTable;
use rollun\utils\IdGenerator;
use Zend\Db\TableGateway\TableGateway;

class AutoIdTable extends SerializedDbTable
{
    const GEN_ID_MAX_TRY = 5;

    /**
     * @var IdGenerator
     */
    protected $idGenerator;

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
     * Generates an arbitrary length string of cryptographic random bytes
     * @return string
     */
    protected function generateId()
    {
        $tryCount = 0;
        do {
            $id = $this->idGenerator->generate();
            $tryCount++;
        } while($this->has($id) || $tryCount < static::GEN_ID_MAX_TRY);
        return $id;
    }

    /**
     * Generate id to item
     * @param array $itemData
     * @return void
     */
    protected function setItemId(array &$itemData)
    {
        if(!isset($itemData[$this->getIdentifier()])) {
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
     * @param array $itemData
     * @param bool $rewriteIfExist
     * @return array|mixed|null
     */
    public function create($itemData, $rewriteIfExist = false)
    {
        if($this->isDataMultipleData($itemData)) {
            foreach ($itemData as &$datum) {
                $this->setItemId($datum);
            }
        } else {
            $this->setItemId($itemData);
        }
        return parent::create($itemData, $rewriteIfExist);
    }

    /**
     * @param array $itemData
     * @param bool $createIfAbsent
     * @return array
     */
    public function update($itemData, $createIfAbsent = false)
    {
        if($this->isDataMultipleData($itemData)) {
            foreach ($itemData as &$datum) {
                $this->setItemId($datum);
            }
        } else {
            $this->setItemId($itemData);
        }
        return parent::update($itemData, $createIfAbsent);
    }
}
