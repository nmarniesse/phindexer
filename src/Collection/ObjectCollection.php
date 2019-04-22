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
use NMarniesse\Phindexer\Util\InflectorBuilder;

/**
 * Class ObjectCollection
 *
 * Define a collection of objects
 *
 * @package NMarniesse\Phindexer\Collection
 * @author  Nicolas Marniesse <nicolas.marniesse@gmail.com>
 */
class ObjectCollection extends AbstractCollection implements CollectionInterface
{
    /** @var array */
    protected $property_fingerprints = [];

    /**
     * @param string $property
     * @return CollectionInterface
     */
    public function addPropertyIndex(string $property): CollectionInterface
    {
        $expression = $this->buildIndex($property);
        $this->property_fingerprints[$property] = $expression->getFingerprint();

        return $this->addExpressionIndex($expression);
    }

    /**
     * @param string $property
     * @param string $value
     * @return CollectionInterface
     */
    public function findWhere(string $property, string $value): CollectionInterface
    {
        $fingerprint = $this->property_fingerprints[$property] ?? null;
        if ($fingerprint === null) {
            $this->addPropertyIndex($property);
            $fingerprint = $this->property_fingerprints[$property] ?? null;
        }

        $storage = $this->index_storages[$fingerprint] ?? null;
        if (!$storage instanceof StorageInterface) {
            throw new \RuntimeException(sprintf("Storage not found for property index '%s'.", $property));
        }

        return new ObjectCollection($storage->getResults($value));
    }

    /**
     * buildIndex
     *
     * @param string $property_name
     * @return ExpressionIndex
     */
    protected function buildIndex(string $property_name): ExpressionIndex
    {
        return new ExpressionIndex(function ($item) use ($property_name) {
            $reflection = new \ReflectionObject($item);
            $property_object = $reflection->getProperty($property_name);
            if ($property_object instanceof \ReflectionProperty && $property_object->isPublic()) {
                return $item->$property_name;
            }

            $inflector = InflectorBuilder::build();
            $method    = $inflector->camelize(sprintf('get_%s', $property_name));
            if (method_exists($item, $method)) {
                return $item->$method();
            }

            throw new \RuntimeException(sprintf('Undefined property: %s', $property_name));
        });
    }
}
