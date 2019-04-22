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
     * getPlanetCollection
     *
     * @return \Iterator
     */
    public static function getPlanetCollection(): \Iterator
    {
        return new \ArrayIterator([
            new Planet('Earth', 'Solar system', 365),
            new Planet('Mars', 'Solar system', 686.885),
            new Planet('Venus', 'Solar system', 583.92),
            new Planet('Kepler 186-f', 'Kepler 186 system', 129.9),
        ]);
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
                ->isInstanceOf(\ReflectionException::class)->message->contains('Property unknown does not exist')

            ->assert('A property index can be added if property is private but public access method exists.')
            ->given($tested_instance = new TestedClass(static::getPlanetCollection()))
            ->when($res = $tested_instance->addPropertyIndex('system'))
                ->object($res)->isInstanceOf(TestedClass::class)
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
            ->given($tested_instance = new TestedClass(static::getPlanetCollection()))
            ->and($expression = new ExpressionIndex(function (Planet $planet) {
                return strtolower($planet->getSystem()) === 'solar system';
            }))
            ->when($res = $tested_instance->addExpressionIndex($expression))
                ->object($res)->isInstanceOf(TestedClass::class)
            ;
    }

    /**
     * testFindWhereExpression
     */
    public function testFindWhereExpression()
    {
        $this
            ->assert('Use index to return results.')
            ->given($tested_instance = new TestedClass(static::getPlanetCollection()))
            ->and($expression = new ExpressionIndex(function (Planet $planet) {
                return strtolower($planet->getSystem()) === 'solar system';
            }))
            ->and($tested_instance->addExpressionIndex($expression))
            ->when($res = $tested_instance->findWhereExpression($expression, true))
                ->object($res)->isInstanceOf(TestedClass::class)
                ->boolean($this->collectionIsEmpty($res))->isFalse
                ->boolean($this->containsPlanet($res, 'Earth'))->isTrue
                ->boolean($this->containsPlanet($res, 'Mars'))->isTrue
                ->boolean($this->containsPlanet($res, 'Venus'))->isTrue
                ->boolean($this->containsPlanet($res, 'Kepler 186-f'))->isFalse

            ->when($res = $tested_instance->findWhereExpression($expression, false))
                ->object($res)->isInstanceOf(TestedClass::class)
                ->boolean($this->collectionIsEmpty($res))->isFalse
                ->boolean($this->containsPlanet($res, 'Earth'))->isFalse
                ->boolean($this->containsPlanet($res, 'Mars'))->isFalse
                ->boolean($this->containsPlanet($res, 'Venus'))->isFalse
                ->boolean($this->containsPlanet($res, 'Kepler 186-f'))->isTrue

            ->given($expression = new ExpressionIndex(function (Planet $planet) {
                return strtolower($planet->getSystem()) === 'kepler 186 system';
            }))
            ->when($res = $tested_instance->findWhereExpression($expression, true))
                ->object($res)->isInstanceOf(TestedClass::class)
                ->boolean($this->collectionIsEmpty($res))->isFalse
                ->boolean($this->containsPlanet($res, 'Earth'))->isFalse
                ->boolean($this->containsPlanet($res, 'Mars'))->isFalse
                ->boolean($this->containsPlanet($res, 'Venus'))->isFalse
                ->boolean($this->containsPlanet($res, 'Kepler 186-f'))->isTrue
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
     * @param string      $planet_name
     * @return bool
     */
    protected function containsPlanet(TestedClass $collection, string $planet_name): bool
    {
        foreach ($collection as $planet) {
            if ($planet->getName() === $planet_name) {
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
