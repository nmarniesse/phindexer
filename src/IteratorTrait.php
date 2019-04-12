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

/**
 * Trait IteratorTrait
 *
 * @package NMarniesse\Phindexer
 */
trait IteratorTrait
{
    /** @var \iterable */
    protected $iterator;

    /** @var int */
    protected $position = 0;

    /**
     * rewind
     */
    public function rewind(): void
    {
        $this->position = 0;
    }

    /**
     * @return mixed
     */
    public function current()
    {
        return $this->iterator[$this->position];
    }

    /**
     * @return int
     */
    public function key(): int
    {
        return $this->position;
    }

    /**
     * next
     */
    public function next(): void
    {
        ++$this->position;
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return isset($this->iterator[$this->position]);
    }
}