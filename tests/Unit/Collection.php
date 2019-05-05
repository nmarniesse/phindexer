<?php
/*
 * This file is part of phindexer package.
 *
 * (c) 2018 Nicolas Marniesse <nicolas.marniesse@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace NMarniesse\Phindexer\Test\Unit;

use atoum\test;
use NMarniesse\Phindexer\Collection as TestedClass;
use NMarniesse\Phindexer\IndexType\ExpressionIndex;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Collection
 *
 * @package NMarniesse\Phindexer\Test\Unit
 * @author  Nicolas Marniesse <nicolas.marniesse@gmail.com>
 */
class Collection extends test
{
    /**
     * Returns a collection of associative arrays.
     *
     * @return array
     */
    public static function getAssociativeArrayList(): array
    {
        return [
            ['id' => 1, 'name' => 'Earth', 'system' => 'Solar system', 'period_in_days' => 365],
            ['id' => 2, 'name' => 'Mars', 'system' => 'Solar system', 'period_in_days' => 686.885],
            ['id' => 3, 'name' => 'Venus', 'system' => 'Solar system', 'period_in_days' => 583.92],
            ['id' => 4, 'name' => 'Saturn', 'system' => 'Solar system', 'period_in_days' => 10754],
            ['id' => 5, 'name' => 'Kepler 186-f', 'system' => 'Kepler 186 system', 'period_in_days' => 129.9],
        ];
    }

    /**
     * getPlanetCollection
     *
     * @return array
     */
    public static function getObjectList(): array
    {
        return [
            new Planet(1, 'Earth', 'Solar system', 365),
            new Planet(2, 'Mars', 'Solar system', 686.885),
            new Planet(3, 'Venus', 'Solar system', 583.92),
            new Planet(4, 'Saturn', 'Solar system', 10754),
            new Planet(5, 'Kepler 186-f', 'Kepler 186 system', 129.9),
        ];
    }

    /**
     * getMixedList
     *
     * @return \Iterator
     */
    public static function getMixedList(): \Iterator
    {
        return new \ArrayIterator([
            ['id' => 1, 'name' => 'Earth', 'system' => 'Solar system', 'period_in_days' => 365],
            new Planet(2, 'Mars', 'Solar system', 686.885),
            new Planet(3, 'Venus', 'Solar system', 583.92),
            ['id' => 4, 'name' => 'Saturn', 'system' => 'Solar system', 'period_in_days' => 10754],
            new Planet(5, 'Kepler 186-f', 'Kepler 186 system', 129.9),
        ]);
    }

    /**
     * listDataProvider
     *
     * @return array
     */
    protected function listDataProvider(): array
    {
        return [
            [static::getAssociativeArrayList()],
            [static::getObjectList()],
            [static::getMixedList()],
        ];
    }

    /**
     * testConstruct
     *
     * @param iterable $list
     *
     * @dataProvider listDataProvider
     */
    public function testConstruct(iterable $list)
    {
        $this
            ->assert('Test constructor of Collection class.')
            ->when($tested_instance = new TestedClass($list))
                ->object($tested_instance)->isInstanceOf(TestedClass::class)
            ;
    }

    /**
     * testConstructConstraint
     */
    public function testConstructConstraint()
    {
        $this
            ->assert('Test constructor of Collection class.')
            ->when($tested_instance = new TestedClass(static::getAssociativeArrayList()))
                ->object($tested_instance)->isInstanceOf(TestedClass::class)

            ->assert('Test constructor of Collection class with constraint.')
            ->given($constraint = new Assert\Collection([
                'id'             => new Assert\NotBlank(),
                'name'           => new Assert\NotBlank(),
                'system'         => new Assert\Optional(),
                'period_in_days' => [
                    new Assert\NotBlank(),
                    new Assert\Type('numeric'),
                ],
            ]))
            ->when($tested_instance = new TestedClass(static::getAssociativeArrayList(), $constraint))
                ->object($tested_instance)->isInstanceOf(TestedClass::class)

            ->assert('Test constructor of Collection class fails with constraint.')
            ->given($constraint = new Assert\Collection([
                'id'             => new Assert\NotBlank(),
                'name'           => new Assert\NotBlank(),
                'system'         => new Assert\NotBlank(),
                'radius'         => new Assert\NotBlank(),
                'period_in_days' => [
                    new Assert\NotBlank(),
                    new Assert\Type('numeric'),
                ],
            ]))
            ->exception(function () use ($constraint) {
                return new TestedClass(static::getAssociativeArrayList(), $constraint);
            })
                ->isInstanceOf(\RuntimeException::class)->message->contains('Validation fails:')
            ;
    }

    /**
     * testAddKeyIndex
     *
     * @param iterable $list
     *
     * @dataProvider listDataProvider
     */
    public function testAddKeyIndex(iterable $list)
    {
        $this
            ->assert('A key index can be added.')
            ->given($tested_instance = new TestedClass($list))
            ->when($res = $tested_instance->addKeyIndex('system'))
                ->object($res)->isInstanceOf(TestedClass::class)

            ->assert('Exception when trying to index an unknown key.')
            ->exception(function () use ($tested_instance) {
                $tested_instance->addKeyIndex('unknown');
            })
                ->isInstanceOf(\RuntimeException::class)->hasMessage('Undefined key: unknown');
            ;
    }

    /**
     * testFindWhere
     *
     * @param iterable $list
     *
     * @dataProvider listDataProvider
     */
    public function testFindWhere(iterable $list)
    {
        $this
            ->assert('Use index to return rows kinked to a category.')
            ->given($tested_instance = new TestedClass($list))
            ->and($tested_instance->addKeyIndex('system'))
            ->when($res = $tested_instance->findWhere('system', 'Solar system'))
                ->object($res)->isInstanceOf(TestedClass::class)
                ->boolean($this->collectionIsEmpty($res))->isFalse
                ->boolean($this->contains($res, 1))->isTrue
                ->boolean($this->contains($res, 2))->isTrue
                ->boolean($this->contains($res, 3))->isTrue
                ->boolean($this->contains($res, 4))->isTrue
                ->boolean($this->contains($res, 5))->isFalse

            ->when($res = $tested_instance->findWhere('system', 'unknown system'))
                ->object($res)->isInstanceOf(TestedClass::class)
                ->boolean($this->collectionIsEmpty($res))->isTrue

            ->assert('Create index if not created and return rows linked to a system.')
            ->and($tested_instance->addKeyIndex('system'))
            ->when($res = $tested_instance->findWhere('name', 'Earth'))
                ->object($res)->isInstanceOf(TestedClass::class)

            ->assert('Exception when find on unknown key.')
            ->exception(function () use ($tested_instance) {
                $tested_instance->findWhere('unknown', 'whatever');
            })
                ->isInstanceOf(\RuntimeException::class)->hasMessage('Undefined key: unknown')
            ;
    }

    /**
     * testAddExpressionIndex
     */
    public function testAddExpressionIndex()
    {
        $this
            ->assert('An expression index can be added.')
            ->given($tested_instance = new TestedClass(static::getAssociativeArrayList()))
            ->and($expression = new ExpressionIndex(function ($item) {
                foreach (['name', 'system'] as $key) {
                    if (!array_key_exists($key, $item)) {
                        throw new \RuntimeException(sprintf('Undefined index: %s', $key));
                    }
                }

                return strpos($item['name'], 'E') === 0 && $item['system'] = 'Solar system';
            }))
            ->when($res = $tested_instance->addExpressionIndex($expression))
                ->object($res)->isInstanceOf(TestedClass::class)

            ->assert('Exception when trying to index an unknown key.')
            ->exception(function () use ($tested_instance, $expression) {
                $tested_instance->addExpressionIndex(new ExpressionIndex(function ($item) {
                    foreach (['unknown', 'system'] as $key) {
                        if (!array_key_exists($key, $item)) {
                            throw new \RuntimeException(sprintf('Undefined index: %s', $key));
                        }
                    }

                    return strpos($item['unknown'], 'E') === 0 && $item['system'] = 'Solar system';
                }));
            })
                ->isInstanceOf(\RuntimeException::class)->hasMessage('Undefined index: unknown');
            ;
    }

    /**
     * testFindWhereExpression
     *
     * @param iterable $list
     *
     * @dataProvider listDataProvider
     */
    public function testFindWhereExpression(iterable $list)
    {
        $this
            ->assert('Use index to return results.')
            ->given($tested_instance = new TestedClass($list))
            ->and($expression = new ExpressionIndex(function ($item) {
                if (is_array($item)) {
                    return strpos($item['name'], 'E') === 0 && $item['system'] === 'Solar system';
                }

                if ($item instanceof Planet) {
                    return strpos($item->getName(), 'E') === 0 && $item->getSystem() === 'Solar system';
                }

                throw new \RuntimeException('Type is not handled.');
            }))
            ->and($tested_instance->addExpressionIndex($expression))
            ->when($res = $tested_instance->findWhereExpression($expression, true))
                ->object($res)->isInstanceOf(TestedClass::class)
                ->boolean($this->collectionIsEmpty($res))->isFalse
                ->boolean($this->contains($res, 1))->isTrue
                ->boolean($this->contains($res, 2))->isFalse
                ->boolean($this->contains($res, 3))->isFalse
                ->boolean($this->contains($res, 4))->isFalse
                ->boolean($this->contains($res, 5))->isFalse

            ->when($res = $tested_instance->findWhereExpression($expression, false))
                ->object($res)->isInstanceOf(TestedClass::class)
                ->boolean($this->collectionIsEmpty($res))->isFalse
                ->boolean($this->contains($res, 1))->isFalse
                ->boolean($this->contains($res, 2))->isTrue
                ->boolean($this->contains($res, 3))->isTrue
                ->boolean($this->contains($res, 4))->isTrue
                ->boolean($this->contains($res, 5))->isTrue

            ->when($res = $tested_instance->findWhereExpression($expression, '12'))
                ->object($res)->isInstanceOf(TestedClass::class)
                ->boolean($this->collectionIsEmpty($res))->isTrue
            ;
    }

    /**
     * testAddItem
     */
    public function testAddItem()
    {
        $this
            ->assert('Add items and check they are indexed.')
            ->given($constraint = new Assert\Collection([
                'id'             => new Assert\NotBlank(),
                'name'           => new Assert\NotBlank(),
                'system'         => new Assert\NotBlank(),
                'period_in_days' => [
                    new Assert\NotBlank(),
                    new Assert\Type('numeric'),
                ],
            ]))
            ->and($tested_instance = new TestedClass(static::getAssociativeArrayList(), $constraint))
            ->and($tested_instance->addKeyIndex('system'))
            ->and($tested_instance->addItem([
                'id'             => 10,
                'name'           => 'Jupiter',
                'system'         => 'Solar system',
                'period_in_days' => 4332,
            ]))
            ->and($tested_instance->addItem([
                'id'             => 11,
                'name'           => 'Kepler 186-a',
                'system'         => 'Kepler 186',
                'period_in_days' => 0,
            ]))
            ->when($res = $tested_instance->findWhere('system', 'Solar system'))
                ->object($res)->isInstanceOf(TestedClass::class)
                ->boolean($this->contains($res, 10))->isTrue
                ->boolean($this->contains($res, 11))->isFalse

            ->assert('Add bad item.')
            ->exception(function () use ($tested_instance) {
                $tested_instance->addItem([
                    'id'             => 11,
                    'name'           => 'Mercury',
                    'system'         => 'Solar system',
                ]);
            })
                ->isInstanceOf(\RuntimeException::class)
                    ->message
                        ->contains('Array[period_in_days]')
                        ->contains('This field is missing')
            ;
    }

    /**
     * contains
     *
     * @param TestedClass $collection
     * @param int         $id
     * @return bool
     */
    protected function contains(TestedClass $collection, int $id): bool
    {
        foreach ($collection as $item) {
            if (is_array($item) && $item['id'] === $id) {
                return true;
            }

            if (is_object($item) && $item->getId('id') === $id) {
                return true;
            }
        }

        return false;
    }

    /**
     * collectionIsEmpty
     *
     * @param TestedClass $collection
     * @return bool
     */
    protected function collectionIsEmpty(TestedClass $collection): bool
    {
        foreach ($collection as $item) {
            return false;
        }

        return true;
    }
}
