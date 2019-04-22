<?php
/*
 * This file is part of phindexer package.
 *
 * (c) 2018 Nicolas Marniesse <nicolas.marniesse@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace NMarniesse\Phindexer\Test\Unit\Collection;

use atoum\test;
use NMarniesse\Phindexer\Collection\ObjectCollection as TestedClass;
use NMarniesse\Phindexer\IndexType\ExpressionIndex;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ObjectCollection
 *
 * @package NMarniesse\Phindexer\Test\Unit\Collection
 * @author  Nicolas Marniesse <nicolas.marniesse@gmail.com>
 */
class ObjectCollection extends test
{
    /**
     * Returns a collection of associative arrays.
     *
     * @return array
     */
    public static function getObjects(): array
    {
        $array = [
            ['id' => 1, 'name' => 'A', 'category' => 'enceinte', 'price' => 60],
            ['id' => 2, 'name' => 'B', 'category' => 'enceinte', 'price' => 80],
            ['id' => 3, 'name' => 'C', 'category' => 'ampli', 'price' => 10],
            ['id' => 4, 'name' => 'D', 'category' => 'enceinte', 'price' => 40],
            ['id' => 5, 'name' => 'E', 'category' => null, 'price' => 50],
        ];

        $collection = [];
        foreach ($array as $item) {
            $object = new \stdClass();
            foreach ($item as $property => $value) {
                $object->$property = $value;
            }

            $collection[] = $object;

        }

        return $collection;
    }

    /**
     * testConstruct
     *
     * @tags mine
     */
    public function testConstruct()
    {
        $this
            ->assert('Test constructor of Collection class.')
            ->when($tested_instance = new TestedClass(static::getObjects()))
                ->object($tested_instance)->isInstanceOf(TestedClass::class)

            ->assert('Test constructor of Collection class with constraint.')
            ->given($constraint = new Assert\Type(['type' => '\stdClass']))
            ->when($tested_instance = new TestedClass(static::getObjects(), $constraint))
                ->object($tested_instance)->isInstanceOf(TestedClass::class)

            ->assert('Test constructor of Collection class fails with constraint.')
            ->given($constraint = new Assert\Type(['type' => '\Iterator']))
            ->exception(function () use ($constraint) {
                return new TestedClass(static::getObjects(), $constraint);
            })
                ->isInstanceOf(\RuntimeException::class)->message->contains('Validation fails:')
            ;
    }

    /**
     * testAddPropertyIndex
     */
    public function testAddPropertyIndex()
    {
        $this
            ->assert('A property index can be added.')
            ->given($tested_instance = new TestedClass(static::getObjects()))
            ->when($res = $tested_instance->addPropertyIndex('category'))
                ->object($res)->isInstanceOf(TestedClass::class)

            ->assert('Exception when trying to index an unknown property.')
            ->exception(function () use ($tested_instance) {
                $tested_instance->addPropertyIndex('unknown');
            })
                ->isInstanceOf(\ReflectionException::class)->message->contains('Property unknown does not exist');
            ;
    }

    /**
     * testFindWhere
     */
    public function testFindWhere()
    {
        $this
            ->assert('Use index to return rows kinked to a category.')
            ->given($tested_instance = new TestedClass(static::getObjects()))
            ->and($tested_instance->addPropertyIndex('category'))
            ->when($res = $tested_instance->findWhere('category', 'enceinte'))
                ->object($res)->isInstanceOf(TestedClass::class)
                ->boolean($this->collectionIsEmpty($res))->isFalse
                ->boolean($this->checkResults($res, 'category', 'enceinte'))->isTrue

            ->when($res = $tested_instance->findWhere('category', 'unknown'))
                ->object($res)->isInstanceOf(TestedClass::class)
                ->boolean($this->collectionIsEmpty($res))->isTrue

            ->assert('Create index if not created and return rows linked to a category.')
            ->and($tested_instance->addPropertyIndex('category'))
            ->when($res = $tested_instance->findWhere('name', 'A'))
                ->object($res)->isInstanceOf(TestedClass::class)

            ->assert('Exception when find on unknown property.')
            ->exception(function () use ($tested_instance) {
                $tested_instance->findWhere('unknown', 'enceinte');
            })
                ->isInstanceOf(\ReflectionException::class)->message->contains('Property unknown does not exist');
            ;
    }

    /**
     * testAddExpressionIndex
     */
    public function testAddExpressionIndex()
    {
        $this
            ->assert('An expression index can be added.')
            ->given($tested_instance = new TestedClass(static::getObjects()))
            ->and($expression = new ExpressionIndex(function ($item) {
                $reflection = new \ReflectionObject($item);
                foreach (['price', 'category'] as $property) {
                    $property_object = $reflection->getProperty($property);
                    if ($property_object instanceof \ReflectionProperty && !$property_object->isPublic()) {
                        throw new \RuntimeException(sprintf('Undefined public property: %s', $property));
                    }
                }

                return $item->price > 50 && $item->category = 'enceintes';
            }))
            ->when($res = $tested_instance->addExpressionIndex($expression))
                ->object($res)->isInstanceOf(TestedClass::class)

            ->assert('Exception when trying to index an unknown property.')
            ->exception(function () use ($tested_instance, $expression) {
                $tested_instance->addExpressionIndex(new ExpressionIndex(function ($item) {
                    $reflection = new \ReflectionObject($item);
                    foreach (['unknown', 'category'] as $property) {
                        $property_object = $reflection->getProperty($property);
                        if ($property_object instanceof \ReflectionProperty && !$property_object->isPublic()) {
                            throw new \RuntimeException(sprintf('Undefined public property: %s', $property));
                        }
                    }

                    return $item->unknown > 50 && $item->category = 'enceintes';
                }));
            })
                ->isInstanceOf(\ReflectionException::class)->message->contains('Property unknown does not exist');
            ;
    }

    /**
     * testFindWhereExpression
     */
    public function testFindWhereExpression()
    {
        $this
            ->assert('Use index to return results.')
            ->given($tested_instance = new TestedClass(static::getObjects()))
            ->and($expression = new ExpressionIndex(function ($item) {
                $reflection = new \ReflectionObject($item);
                foreach (['price', 'category'] as $property) {
                    $property_object = $reflection->getProperty($property);
                    if ($property_object instanceof \ReflectionProperty && !$property_object->isPublic()) {
                        throw new \RuntimeException(sprintf('Undefined public property: %s', $property));
                    }
                }

                return $item->price > 50 && $item->category = 'enceinte';
            }))
            ->and($tested_instance->addExpressionIndex($expression))
            ->when($res = $tested_instance->findWhereExpression($expression, true))
                ->object($res)->isInstanceOf(TestedClass::class)
                ->boolean($this->collectionIsEmpty($res))->isFalse
                ->boolean($this->contains($res, 1))->isTrue
                ->boolean($this->contains($res, 2))->isTrue
                ->boolean($this->contains($res, 3))->isFalse
                ->boolean($this->contains($res, 4))->isFalse
                ->boolean($this->contains($res, 5))->isFalse

            ->when($res = $tested_instance->findWhereExpression($expression, false))
                ->object($res)->isInstanceOf(TestedClass::class)
                ->boolean($this->collectionIsEmpty($res))->isFalse
                ->boolean($this->contains($res, 1))->isFalse
                ->boolean($this->contains($res, 2))->isFalse
                ->boolean($this->contains($res, 3))->isTrue
                ->boolean($this->contains($res, 4))->isTrue
                ->boolean($this->contains($res, 5))->isTrue

            ->when($res = $tested_instance->findWhereExpression($expression, '12'))
                ->object($res)->isInstanceOf(TestedClass::class)
                ->boolean($this->collectionIsEmpty($res))->isTrue
            ;
    }

    /**
     * @param TestedClass $collection
     * @param string      $property
     * @param string      $value
     * @return bool
     */
    protected function checkResults(TestedClass $collection, string $property, string $value): bool
    {
        foreach ($collection as $item) {
            if ($item->$property !== $value) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param TestedClass $collection
     * @param int         $id
     * @return bool
     */
    protected function contains(TestedClass $collection, int $id): bool
    {
        foreach ($collection as $item) {
            if ($item->id === $id) {
                return true;
            }
        }

        return false;
    }

    /**
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
