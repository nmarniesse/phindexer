<?php
/*
 * This file is part of phindexer package.
 *
 * (c) 2018 Nicolas Marniesse <nicolas.marniesse@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace NMarniesse\Phindexer\Storage;

use NMarniesse\Phindexer\Util\IndexSanitizer;
use NMarniesse\Phindexer\CollectionInterface;
use NMarniesse\Phindexer\IndexType\ExpressionIndex;

/**
 * Class BtreeIndex
 *
 * @package NMarniesse\Phindexer\Storage
 * @author  Nicolas Marniesse <nicolas.marniesse@gmail.com>
 */
class BtreeStorage implements StorageInterface
{
    /** @var array */
    private $storage = [];

    /** @var ExpressionIndex */
    private $expression_index;

    /**
     * BtreeStorage constructor.
     *
     * @param ExpressionIndex $expression_index
     */
    public function __construct(ExpressionIndex $expression_index)
    {
        $this->expression_index = $expression_index;
    }

    /**
     * @param CollectionInterface $collection
     * @return StorageInterface
     */
    public function addCollectionInStorage(CollectionInterface $collection): StorageInterface
    {
        foreach ($collection as $item) {
            $this->addItemInStorage($item);
        }

        return $this;
    }

    /**
     * @param mixed $item
     * @return StorageInterface
     */
    public function addItemInStorage(&$item): StorageInterface
    {
        $value = IndexSanitizer::sanitize($this->expression_index->getExpressionResult($item));

        if (!array_key_exists($value, $this->storage)) {
            $this->storage[$value] = [];
        }

        $this->storage[$value][] = $item;

        return $this;
    }

    /**
     * @param mixed $value
     * @return array
     */
    public function getResults($value): array
    {
        return $this->storage[IndexSanitizer::sanitize($value)] ?? [];
    }
}
