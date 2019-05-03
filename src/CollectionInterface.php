<?php
/*
 * This file is part of phindexer package.
 *
 * (c) 2018 Nicolas Marniesse <nicolas.marniesse@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace NMarniesse\Phindexer;

use NMarniesse\Phindexer\IndexType\ExpressionIndex;

/**
 * Interface CollectionInterface
 *
 * @package NMarniesse\Phindexer
 */
interface CollectionInterface
{
    /**
     * addItem
     *
     * @param mixed $item
     * @return CollectionInterface
     */
    public function addItem($item): self;

    /**
     * addExpressionIndex
     *
     * @param ExpressionIndex $expression
     * @return CollectionInterface
     */
    public function addExpressionIndex(ExpressionIndex $expression): CollectionInterface;

    /**
     * findWhereExpression
     *
     * @param ExpressionIndex $expression
     * @param mixed           $value
     * @return CollectionInterface
     */
    public function findWhereExpression(ExpressionIndex $expression, $value): CollectionInterface;
}