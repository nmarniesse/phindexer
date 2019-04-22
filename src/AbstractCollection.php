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
use NMarniesse\Phindexer\Validator\ValidatorFactory;
use Symfony\Component\Validator\Constraint;

/**
 * Class AbstractCollection
 *
 * @package NMarniesse\Phindexer
 * @author  Nicolas Marniesse <nicolas.marniesse@gmail.com>
 */
abstract class AbstractCollection implements \Iterator
{
    use IteratorTrait;

    /** @var array */
    protected $index_storages = [];

    /** @var Constraint|null */
    protected $constraint;

    /**
     * ArrayCollection constructor.
     *
     * @param iterable               $iterator
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
     * @return CollectionInterface
     */
    protected function validate($item): CollectionInterface
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
     * @return CollectionInterface
     */
    public function addItem($item): CollectionInterface
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
     * @return CollectionInterface
     */
    private function indexItem($item): CollectionInterface
    {
        /** @var StorageInterface $storage */
        foreach ($this->index_storages as $storage) {
            $storage->addItemInStorage($item);
        }

        return $this;
    }

    /**
     * @param ExpressionIndex $expression
     * @param mixed           $value
     * @return CollectionInterface
     */
    public function findWhereExpression(ExpressionIndex $expression, $value): CollectionInterface
    {
        if (!array_key_exists($expression->getFingerprint(), $this->index_storages)) {
            $this->addExpressionIndex($expression);
        }

        $results    = $this->index_storages[$expression->getFingerprint()]->getResults($value);
        $class_name = get_class($this);

        return new $class_name($results, $this->constraint);
    }

    /**
     * @param ExpressionIndex $expression
     * @return CollectionInterface
     */
    public function addExpressionIndex(ExpressionIndex $expression): CollectionInterface
    {
        $storage = new BtreeStorage($expression);
        $storage->addCollectionInStorage($this);
        $this->index_storages[$expression->getFingerprint()] = $storage;

        return $this;
    }
}
