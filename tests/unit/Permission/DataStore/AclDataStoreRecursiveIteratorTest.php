<?php

namespace rollun\test\unit\Permission\DataStore;

use rollun\permission\DataStore\AclDataStoreIterator;
use PHPUnit\Framework\TestCase;

class AclDataStoreRecursiveIteratorTest extends TestCase
{

    public function dataProvider(): array
    {
        return [
            [
                [
                    ['id' => '1', 'parent_id' => null],
                    ['id' => '2', 'parent_id' => '1'],
                    ['id' => '3', 'parent_id' => '2'],
                ],
                [
                    ['id' => '2', 'parent_id' => '1'],
                    ['id' => '1', 'parent_id' => null],
                    ['id' => '3', 'parent_id' => '2'],
                ],
            ],
            [
                [
                    ['id' => '1', 'parent_id' => null],
                    ['id' => '2', 'parent_id' => null],
                    ['id' => '3', 'parent_id' => null],
                    ['id' => '4', 'parent_id' => '1'],
                    ['id' => '5', 'parent_id' => '2'],
                    ['id' => '6', 'parent_id' => '4'],
                    ['id' => '7', 'parent_id' => '5'],
                    ['id' => '8', 'parent_id' => '5'],
                ],
                [
                    ['id' => '5', 'parent_id' => '2'],
                    ['id' => '7', 'parent_id' => '5'],
                    ['id' => '2', 'parent_id' => null],
                    ['id' => '6', 'parent_id' => '4'],
                    ['id' => '4', 'parent_id' => '1'],
                    ['id' => '3', 'parent_id' => null],
                    ['id' => '8', 'parent_id' => '5'],
                    ['id' => '1', 'parent_id' => null],
                ],
            ]

        ];
    }

    /**
     * @param $expected
     * @param $dataset
     * @dataProvider dataProvider
     */
    public function testIterator($expected, $dataset): void
    {
        $iterator = new AclDataStoreIterator($dataset);
        $this->assertEquals($expected, iterator_to_array($iterator));
    }
}