<?php
/*
 * This file is part of phindexer package.
 *
 * (c) 2018 Nicolas Marniesse <nicolas.marniesse@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace NMarniesse\Phindexer\Test\Unit\Fixture;

/**
 * Class FixtureProvider
 *
 * @package NMarniesse\Phindexer\Test\Unit\Fixture
 * @author  Nicolas Marniesse <nicolas.marniesse@phc-holding.com>
 */
class FixtureProvider
{
    /**
     * Returns a collection of associative arrays.
     *
     * @return array
     */
    public static function getAssociativeArray(): array
    {
        return [
            ['id' => 1, 'name' => 'A', 'category' => 'enceinte', 'price' => 60],
            ['id' => 2, 'name' => 'B', 'category' => 'enceinte', 'price' => 80],
            ['id' => 3, 'name' => 'C', 'category' => 'ampli', 'price' => 10],
            ['id' => 4, 'name' => 'D', 'category' => 'enceinte', 'price' => 40],
            ['id' => 5, 'name' => 'E', 'category' => null, 'price' => 50],
        ];
    }
}