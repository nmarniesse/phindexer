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
use NMarniesse\Phindexer\Storage\BtreeStorage;
use NMarniesse\Phindexer\Storage\StorageInterface;

/**
 * Class Collection
 *
 * @package NMarniesse\Phindexer
 * @author  Nicolas Marniesse <nicolas.marniesse@phc-holding.com>
 */
class Collection implements \Iterator
{
    use IteratorTrait;

    /** @var array */
    private $index_storages = [];

    /** @var array */
    private $column_fingerprints = [];

    /**
     * Collection constructor.
     *
     * @param iterable $iterator
     */
    public function __construct(iterable $iterator)
    {
        $this->iterator = $iterator;
        $this->position = 0;
    }

    /**
     * @return iterable
     */
    public function getIterator(): iterable
    {
        return $this->iterator;
    }

    /**
     * @param mixed $item
     * @return Collection
     */
    public function addItem($item): self
    {
        if (is_array($this->iterator)) {
            $this->indexItem($item);
            $this->iterator[] = $item;

            return $this;
        }

        if ($this->iterator instanceof \ArrayIterator) {
            $this->indexItem($item);
            $this->iterator->append($item);

            return $this;
        }

        throw new \RuntimeException('Can not add item on the iterator.');
    }

    /**
     * @param string $column
     * @return Collection
     */
    public function addColumnIndex(string $column): self
    {
        $expression = new ExpressionIndex(function ($item) use ($column) {
            if (!array_key_exists($column, $item)) {
                throw new \RuntimeException(sprintf('Undefined index: %s', $column));
            }

            return $item[$column];
        });

        $this->column_fingerprints[$column] = $expression->getFingerprint();

        return $this->addExpressionIndex($expression);
    }

    /**
     * @param string $column
     * @param string $value
     * @return Collection
     */
    public function findWhere(string $column, string $value): Collection
    {
        $fingerprint = $this->column_fingerprints[$column] ?? null;
        if ($fingerprint === null) {
            $this->addColumnIndex($column);
            $fingerprint = $this->column_fingerprints[$column] ?? null;
        }

        $storage = $this->index_storages[$fingerprint] ?? null;
        if (!$storage instanceof StorageInterface) {
            throw new \RuntimeException(sprintf("Storage not found for column index '%s'.", $column));
        }

        return new Collection($storage->getResults($value));
    }

    /**
     * @param string $column
     * @return Collection
     */
    public function addColumnUniqueIndex(string $column): self
    {
        throw new \RuntimeException('Not implemented yet.');
    }

    /**
     * @param ExpressionIndex $expression
     * @return Collection
     */
    public function addExpressionIndex(ExpressionIndex $expression): self
    {
        $storage = new BtreeStorage($expression);
        $storage->addCollectionInStorage($this);
        $this->index_storages[$expression->getFingerprint()] = $storage;

        return $this;
    }

    /**
     * @param ExpressionIndex $expression
     * @param string          $value
     * @return Collection
     */
    public function findWhereExpression(ExpressionIndex $expression, string $value): Collection
    {
        return new Collection($this->index_storages[$expression->getFingerprint()]->getResults($value));
    }

    /**
     * @param $item
     * @return Collection
     */
    private function indexItem($item): self
    {
        /** @var StorageInterface $storage */
        foreach ($this->index_storages as $storage) {
            $storage->addItemInStorage($item);
        }

        return $this;
    }
}
