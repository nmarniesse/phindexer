<?php
/*
 * This file is part of phindexer package.
 *
 * (c) 2018 Nicolas Marniesse <nicolas.marniesse@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace NMarniesse\Phindexer\Test\Unit\IndexType;

use atoum\test;
use NMarniesse\Phindexer\IndexType\ExpressionIndex as TestedClass;

/**
 * Class ExpressionIndex
 *
 * @package NMarniesse\Phindexer\Test\Unit\IndexType
 * @author  Nicolas Marniesse <nicolas.marniesse@phc-holding.com>
 */
class ExpressionIndex extends test
{
    /**
     * testConstruct
     */
    public function testConstruct()
    {
        $this
            ->assert('Test constructor of Collection class.')
            ->given($callable = function ($item) {
                if (!array_key_exists('id', $item)) {
                    throw new \InvalidArgumentException('error');
                }

                return $item['id'];
            })
            ->when($tested_instance = new TestedClass($callable))
                ->object($tested_instance)->isInstanceOf(TestedClass::class)
            ;
    }

    /**
     * testConstruct
     */
    public function testGetFingerprint()
    {
        $this
            ->assert('Each ExpressionIndex has different fingerprints.')
            ->given($callable = function ($item) {
                if (!array_key_exists('id', $item)) {
                    throw new \InvalidArgumentException('error');
                }

                return $item['id'];
            })
            ->given($tested_instance = new TestedClass($callable))
            ->when($fingerprint = $tested_instance->getFingerprint())
                ->string($fingerprint)
            ->given($tested_instance2 = new TestedClass($callable))
            ->when($fingerprint2 = $tested_instance2->getFingerprint())
                ->string($fingerprint2)->isNotEqualTo($fingerprint)
            ;
    }

    /**
     * testGetExpressionResult
     */
    public function testGetExpressionResult()
    {
        $this
            ->assert('Each ExpressionIndex has different fingerprints.')
            ->given($callable = function ($item) {
                if (!array_key_exists('id', $item)) {
                    throw new \InvalidArgumentException('error');
                }

                return $item['id'];
            })
            ->given($tested_instance = new TestedClass($callable))
            ->when($result = $tested_instance->getExpressionResult(['id' => 5]))
                ->integer($result)->isEqualTo(5)

            ->exception(function () use ($tested_instance) {
                $tested_instance->getExpressionResult(['name' => 5]);
            })
                ->isInstanceOf(\InvalidArgumentException::class)->hasMessage('error')
            ;
    }
}
