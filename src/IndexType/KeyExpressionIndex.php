<?php
/*
 * This file is part of phindexer package.
 *
 * (c) 2018 Nicolas Marniesse <nicolas.marniesse@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NMarniesse\Phindexer\IndexType;

use NMarniesse\Phindexer\Util\InflectorFactory;

/**
 * Class KeyExpressionIndex
 *
 * @package NMarniesse\Phindexer\IndexType
 * @author  Nicolas Marniesse <nicolas.marniesse@phc-holding.com>
 */
final class KeyExpressionIndex
{
    private $key;

    /**
     * KeyExpressionIndex constructor.
     *
     * @param string $key
     */
    public function __construct(string $key)
    {
        $this->key = $key;
    }

    /**
     * __invoke
     *
     * @param mixed $item
     * @return mixed
     */
    public function __invoke($item)
    {
        if (is_array($item)) {
            return $this->executeOnArray($item);
        } elseif (is_object($item)) {
            return $this->executeOnObject($item);
        }

        throw new \RuntimeException('Item must be either an array or an object.');
    }

    /**
     * executeOnArray
     *
     * @param array $item
     * @return mixed
     */
    private function executeOnArray(array $item)
    {
        if (!array_key_exists($this->key, $item)) {
            throw new \RuntimeException(sprintf('Undefined key: %s', $this->key));
        }

        return $item[$this->key];
    }

    /**
     * executeOnObject
     *
     * @param $item
     * @return mixed
     */
    private function executeOnObject($item)
    {
        try {
            $reflection      = new \ReflectionObject($item);
            $property_object = $reflection->getProperty($this->key);
            if ($property_object instanceof \ReflectionProperty && $property_object->isPublic()) {
                return $item->{$this->key};
            }
        } catch (\ReflectionException $e) {
            throw new \RuntimeException(sprintf('Undefined key: %s', $this->key));
        }

        $inflector = InflectorFactory::build();
        $method    = $inflector->camelize(sprintf('get_%s', $this->key));
        if (method_exists($item, $method)) {
            return $item->$method();
        }

        throw new \RuntimeException(sprintf('Undefined key: %s', $this->key));
    }
}
