<?php
/*
 * This file is part of phindexer package.
 *
 * (c) 2018 Nicolas Marniesse <nicolas.marniesse@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace NMarniesse\Phindexer\Test\Unit\Storage;

use atoum\test;
use NMarniesse\Phindexer\Collection\ArrayCollection;
use NMarniesse\Phindexer\IndexType\ExpressionIndex;
use NMarniesse\Phindexer\Storage\HashStorage as TestedClass;

/**
 * Class HashStorage
 *
 * @package NMarniesse\Phindexer\Test\Unit\Storage
 * @author  Nicolas Marniesse <nicolas.marniesse@gmail.com>
 */
class HashStorage extends test
{
    /**
     * testConstruct
     */
    public function testConstruct()
    {
        $this
            ->assert('Test constructor of Collection class.')
            ->given($callable = function ($item) {
                if (!array_key_exists('id', $item)) {
                    throw new \InvalidArgumentException('error');
                }

                return $item['id'];
            })
            ->and($expression_index = new ExpressionIndex($callable))
            ->when($tested_instance = new TestedClass($expression_index))
                ->object($tested_instance)->isInstanceOf(TestedClass::class)
            ;
    }

    /**
     * testAddItemInStorage
     */
    public function testAddItemInStorage()
    {
        $this
            ->assert('Add item in the storage.')
            ->given($callable = function ($item) {
                if (!array_key_exists('id', $item)) {
                    throw new \InvalidArgumentException('error');
                }

                return $item['id'];
            })
            ->and($expression_index = new ExpressionIndex($callable))
            ->and($tested_instance = new TestedClass($expression_index))
            ->and($item = ['id' => 12])
            ->when($res = $tested_instance->addItemInStorage($item))
                ->object($res)->isInstanceOf(TestedClass::class)

            ->assert('Add bad item in the storage.')
            ->and($item = ['name' => 'test'])
            ->exception(function () use ($tested_instance, $item) {
                $tested_instance->addItemInStorage($item);
            })
                ->isInstanceOf(\InvalidArgumentException::class)
            ;
    }

    /**
     * testAddCollectionInStorage
     */
    public function testAddCollectionInStorage()
    {
        $this
            ->assert('Add item in the storage.')
            ->given($callable = function ($item) {
                if (!array_key_exists('id', $item)) {
                    throw new \InvalidArgumentException('error');
                }

                return $item['id'];
            })
            ->and($expression_index = new ExpressionIndex($callable))
            ->and($tested_instance = new TestedClass($expression_index))
            ->and($collection = new ArrayCollection([
                ['id' => 12],
                ['id' => 20],
            ]))
            ->when($res = $tested_instance->addCollectionInStorage($collection))
                ->object($res)->isInstanceOf(TestedClass::class)

            ->assert('Add bad item in the storage.')
            ->and($collection = new ArrayCollection([
                ['name' => 'test'],
                ['name' => 'test2'],
            ]))
            ->exception(function () use ($tested_instance, $collection) {
                $tested_instance->addCollectionInStorage($collection);
            })
                ->isInstanceOf(\InvalidArgumentException::class)
            ;
    }

    /**
     * testGetResults
     */
    public function testGetResults()
    {
        $this
            ->assert('Add items and check we can retrieve them.')
            ->given($callable = function ($item) {
                if (!array_key_exists('id', $item)) {
                    throw new \InvalidArgumentException('error');
                }

                return $item['id'];
            })
            ->and($expression_index = new ExpressionIndex($callable))
            ->and($tested_instance = new TestedClass($expression_index))
            ->and($collection = new ArrayCollection([
                ['id' => 12],
                ['id' => 20],
            ]))
            ->and($res = $tested_instance->addCollectionInStorage($collection))
            ->and($item = ['id' => 30])
            ->and($res = $tested_instance->addItemInStorage($item))
            ->when($res = $tested_instance->getResults(12))
                ->array($res)
                    ->hasSize(1)
                    ->integer($res[0]['id'])->isEqualTo(12)
            ->when($res = $tested_instance->getResults(30))
                ->array($res)
                    ->hasSize(1)
                    ->integer($res[0]['id'])->isEqualTo(30)
            ->when($res = $tested_instance->getResults(99))
                ->array($res)->hasSize(0)
        ;
    }
}
