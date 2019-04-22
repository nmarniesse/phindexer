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

use Ramsey\Uuid\Uuid;

/**
 * Class ExpressionIndex
 *
 * @package NMarniesse\Phindexer\IndexType
 * @author  Nicolas Marniesse <nicolas.marniesse@gmail.com>
 */
class ExpressionIndex
{
    /**
     * @var callable
     */
    private $callable;

    /** @var string */
    private $fingerprint;

    /**
     * ExpressionIndex constructor.
     *
     * @param callable $callable
     */
    public function __construct(callable $callable)
    {
        $this->callable    = $callable;
        $this->fingerprint = (Uuid::uuid4())->toString();
    }

    /**
     * @return string
     */
    public function getFingerprint(): string
    {
        return $this->fingerprint;
    }

    /**
     * @param mixed $row
     * @return mixed
     */
    public function getExpressionResult($row)
    {
        return call_user_func_array($this->callable, [$row]);
    }
}
