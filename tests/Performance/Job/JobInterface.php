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
 * Interface JobInterface
 *
 * @package NMarniesse\Phindexer\Test\Performance\Job
 */
interface JobInterface
{
    /**
     * @param ExpressionIndex $expression_index
     * @param string          $search_value
     * @return iterable
     */
    public function run(ExpressionIndex $expression_index, string $search_value): iterable;
}
