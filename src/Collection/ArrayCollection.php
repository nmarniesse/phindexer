<?php
/*
 * This file is part of phindexer package.
 *
 * (c) 2018 Nicolas Marniesse <nicolas.marniesse@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace NMarniesse\Phindexer\Collection;

use NMarniesse\Phindexer\AbstractCollection;
use NMarniesse\Phindexer\CollectionInterface;
use NMarniesse\Phindexer\IndexType\ExpressionIndex;
use NMarniesse\Phindexer\Storage\StorageInterface;

/**
 * Class ArrayCollection
 *
 * Define a collection of (associative) arrays
 *
 * @package NMarniesse\Phindexer\Collection
 * @author  Nicolas Marniesse <nicolas.marniesse@gmail.com>
 */
class ArrayCollection extends AbstractCollection implements CollectionInterface
{
    /** @var array */
    protected $fingerprints = [];

    /**
     * @param string $column
     * @return CollectionInterface
     */
    public function addColumnIndex(string $column): CollectionInterface
    {
        $expression = new ExpressionIndex(function ($item) use ($column) {
            if (!array_key_exists($column, $item)) {
                throw new \RuntimeException(sprintf('Undefined index: %s', $column));
            }

            return $item[$column];
        });

        $this->fingerprints[$column] = $expression->getFingerprint();

        return $this->addExpressionIndex($expression);
    }

    /**
     * @param string $column
     * @param string $value
     * @return CollectionInterface
     */
    public function findWhere(string $column, string $value): CollectionInterface
    {
        $fingerprint = $this->fingerprints[$column] ?? null;
        if ($fingerprint === null) {
            $this->addColumnIndex($column);
            $fingerprint = $this->fingerprints[$column] ?? null;
        }

        $storage = $this->index_storages[$fingerprint] ?? null;
        if (!$storage instanceof StorageInterface) {
            throw new \RuntimeException(sprintf("Storage not found for column index '%s'.", $column));
        }

        return new ArrayCollection($storage->getResults($value));
    }

    /**
     * @param string $column
     * @return CollectionInterface
     */
    public function addColumnUniqueIndex(string $column): CollectionInterface
    {
        throw new \RuntimeException('Not implemented yet.');
    }
}
