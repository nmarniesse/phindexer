<?php
/*
 * This file is part of phindexer package.
 *
 * (c) 2018 Nicolas Marniesse <nicolas.marniesse@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NMarniesse\Phindexer\Test\Performance\Job;

use NMarniesse\Phindexer\IndexType\ExpressionIndex;

/**
 * Class ClassicJob
 *
 * @package NMarniesse\Phindexer\Test\Performance\Job
 * @author  Nicolas Marniesse <nicolas.marniesse@phc-holding.com>
 */
class ClassicJob implements JobInterface
{
    /**
     * @var array
     */
    private $collection;

    /**
     * Job constructor.
     *
     * @param array $collection
     */
    public function __construct(array $collection)
    {
        $this->collection = $collection;
    }

    /**
     * @param ExpressionIndex $expression_index
     * @param string          $search_value
     * @return iterable
     */
    public function run(ExpressionIndex $expression_index, string $search_value): iterable
    {
        $res = [];
        foreach ($this->collection as $row) {
            if ($expression_index->getExpressionResult($row) === $search_value) {
                $res[] = $row;
            }
        }

        return $res;
    }
}
