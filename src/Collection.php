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
use NMarniesse\Phindexer\IndexType\KeyExpressionIndex;
use NMarniesse\Phindexer\Storage\HashStorage;
use NMarniesse\Phindexer\Storage\StorageInterface;
use NMarniesse\Phindexer\Validator\ValidatorFactory;
use Symfony\Component\Validator\Constraint;

/**
 * Class AbstractCollection
 *
 * @package NMarniesse\Phindexer
 * @author  Nicolas Marniesse <nicolas.marniesse@gmail.com>
 */
class Collection implements \Iterator
{
    use IteratorTrait;

    /**
     * @var array
     */
    protected $index_storages = [];

    /**
     * @var array
     */
    protected $index_fingerprints = [];

    /**
     * @var Constraint|null
     */
    protected $constraint;

    /**
     * Collection constructor.
     *
     * @param iterable        $iterator
     * @param Constraint|null $constraint
     */
    public function __construct(iterable $iterator, Constraint $constraint = null)
    {
        $this->iterator   = $iterator;
        $this->position   = 0;
        $this->constraint = $constraint;

        foreach ($this->iterator as $item) {
            $this->validate($item);
        }
    }

    /**
     * @return iterable
     */
    public function getIterator(): iterable
    {
        return $this->iterator;
    }

    /**
     * @param $item
     * @return Collection
     */
    protected function validate($item): Collection
    {
        if ($this->constraint instanceof Constraint) {
            $errors = ValidatorFactory::getValidator()->validate($item, $this->constraint);

            if (count($errors) > 0) {
                throw new \RuntimeException(sprintf('Validation fails: %s', (string) $errors));
            }
        }

        return $this;
    }

    /**
     * @param mixed $item
     * @return Collection
     */
    public function addItem($item): Collection
    {
        $this->validate($item);

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
     * @param $item
     * @return Collection
     */
    private function indexItem($item): Collection
    {
        /** @var StorageInterface $storage */
        foreach ($this->index_storages as $storage) {
            $storage->addItemInStorage($item);
        }

        return $this;
    }

    /**
     * addKeyIndex
     *
     * @param string $key
     * @return Collection
     */
    public function addKeyIndex(string $key): Collection
    {
        $expression = new ExpressionIndex(new KeyExpressionIndex($key));

        $this->index_fingerprints[$key] = $expression->getFingerprint();

        return $this->addExpressionIndex($expression);
    }

    /**
     * findWhere
     *
     * @param string $key
     * @param string $value
     * @return Collection
     */
    public function findWhere(string $key, string $value): Collection
    {
        $fingerprint = $this->index_fingerprints[$key] ?? null;
        if ($fingerprint === null) {
            $this->addKeyIndex($key);
            $fingerprint = $this->index_fingerprints[$key] ?? null;
        }

        $storage = $this->index_storages[$fingerprint] ?? null;
        if (!$storage instanceof StorageInterface) {
            throw new \RuntimeException(sprintf("Storage not found for key index '%s'.", $key));
        }

        return new Collection($storage->getResults($value));
    }

    /**
     * addKeyUniqueIndex
     *
     * @param string $key
     * @return Collection
     */
    public function addKeyUniqueIndex(string $key): Collection
    {
        throw new \RuntimeException('Not implemented yet.');
    }

    /**
     * @param ExpressionIndex $expression
     * @return Collection
     */
    public function addExpressionIndex(ExpressionIndex $expression): Collection
    {
        $storage = new HashStorage($expression);
        $storage->addCollectionInStorage($this);
        $this->index_storages[$expression->getFingerprint()] = $storage;

        return $this;
    }

    /**
     * @param ExpressionIndex $expression
     * @param mixed           $value
     * @return Collection
     */
    public function findWhereExpression(ExpressionIndex $expression, $value): Collection
    {
        if (!array_key_exists($expression->getFingerprint(), $this->index_storages)) {
            $this->addExpressionIndex($expression);
        }

        $results    = $this->index_storages[$expression->getFingerprint()]->getResults($value);
        $class_name = get_class($this);

        return new $class_name($results, $this->constraint);
    }
}
