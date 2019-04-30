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
 * Class ProfilerJob
 *
 * @package NMarniesse\Phindexer\Test\Performance\Job\Decorator
 * @author  Nicolas Marniesse <nicolas.marniesse@phc-holding.com>
 */
class ProfilerJob implements JobInterface
{
    /**
     * @var JobInterface
     */
    private $job;

    /**
     * @var int
     */
    private $consumed_memory = 0;

    /**
     * @var float
     */
    private $duration  = 0;

    /**
     * ProfilerJob constructor.
     *
     * @param JobInterface $job
     */
    public function __construct(JobInterface $job)
    {
        $this->job = $job;
    }

    /**
     * getConsumedMemory
     *
     * @return int
     */
    public function getConsumedMemory(): int
    {
        return $this->consumed_memory;
    }

    /**
     * getDuration
     *
     * @return float
     */
    public function getDuration(): float
    {
        return $this->duration;
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
        $memory     = memory_get_usage();
        $time_start = microtime(true);

        $res = $this->job->run($expression_index, $search_value);

        $this->consumed_memory = max(memory_get_usage() - $memory, 0);
        $this->duration        = max(microtime(true) - $time_start, 0);

        return $res;
    }
}
