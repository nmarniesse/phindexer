<?php
/*
 * This file is part of phindexer package.
 *
 * (c) 2018 Nicolas Marniesse <nicolas.marniesse@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace NMarniesse\Phindexer\Test\Performance\Job\Decorator;

use NMarniesse\Phindexer\IndexType\ExpressionIndex;
use NMarniesse\Phindexer\Test\Performance\Job\JobInterface;

/**
 * Class RepetitiveJob
 *
 * @package NMarniesse\Phindexer\Test\Performance\Job\Decorator
 * @author  Nicolas Marniesse <nicolas.marniesse@phc-holding.com>
 */
class RepetitiveJob implements JobInterface
{
    /**
     * @var JobInterface
     */
    private $job;

    /**
     * @var int
     */
    private $repetition;

    /**
     * RepetitiveJob constructor.
     *
     * @param JobInterface $job
     * @param int          $repetition
     */
    public function __construct(JobInterface $job, int $repetition)
    {
        $this->job        = $job;
        $this->repetition = $repetition;
    }

    /**
     * run
     *
     * @param ExpressionIndex $expression_index
     * @param string          $search_value
     * @return iterable
     */
    public function run(ExpressionIndex $expression_index, string $search_value): iterable
    {
        $res = [];
        for ($i = 0; $i < $this->repetition; $i++) {
            $res = $this->job->run($expression_index, $search_value);
        }

        return $res;
    }
}
