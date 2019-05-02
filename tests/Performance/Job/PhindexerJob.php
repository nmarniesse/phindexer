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

use NMarniesse\Phindexer\Collection\ArrayCollection;
use NMarniesse\Phindexer\CollectionInterface;
use NMarniesse\Phindexer\IndexType\ExpressionIndex;

/**
 * Class PhindexerJob
 *
 * @package NMarniesse\Phindexer\Test\Performance\Job
 * @author  Nicolas Marniesse <nicolas.marniesse@phc-holding.com>
 */
class PhindexerJob implements JobInterface
{
    /**
     * @var CollectionInterface
     */
    private $collection;

    /**
     * @var ExpressionIndex
     */
    private $expression_index;

    /**
     * @var bool
     */
    private $is_index_set = false;

    /**
     * Job constructor.
     *
     * @param array           $collection
     * @param ExpressionIndex $expression_index
     */
    public function __construct(array $collection, ExpressionIndex $expression_index)
    {
        $this->collection       = new ArrayCollection($collection);
        $this->expression_index = $expression_index;
    }

    /**
     * @param ExpressionIndex $expression_index
     * @param string          $search_value
     * @return iterable
     */
    public function run(ExpressionIndex $expression_index, string $search_value): iterable
    {
        if (!$this->is_index_set) {
            $this->collection->addExpressionIndex($this->expression_index);
            $this->is_index_set = true;
        }

        return $this->collection->findWhereExpression($expression_index, $search_value);
    }
}
