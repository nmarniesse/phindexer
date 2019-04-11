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

use NMarniesse\Phindexer\Collection;

/**
 * Interface StorageInterface
 *
 * @package NMarniesse\Phindexer\Storage
 */
interface StorageInterface
{
    /**
     * @param Collection $collection
     * @return StorageInterface
     */
    public function addCollectionInStorage(Collection $collection): StorageInterface;

    /**
     * @param mixed $item
     * @return BtreeStorage
     */
    public function addItemInStorage(&$item): StorageInterface;

    /**
     * @param mixed $value
     * @return array
     */
    public function getResults($value): array;
}