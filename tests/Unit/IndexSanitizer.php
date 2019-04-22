<?php
/*
 * This file is part of PhpStorm package.
 *
 * (c) 2019 Nicolas Marniesse
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace NMarniesse\Phindexer\Test\Unit;

use atoum\test;
use NMarniesse\Phindexer\IndexSanitizer as TestedClass;

/**
 * Class IndexSanitizer
 *
 * @package NMarniesse\Phindexer\Test\Unit
 * @author Nicolas Marniesse <nicolas.marniesse@gmail.com>
 */
class IndexSanitizer extends test
{
    /**
     * testSanitize
     */
    public function testSanitize()
    {
        $this
            ->assert('Sanitize a string.')
            ->when($res = TestedClass::sanitize('test'))
                ->string($res)
            ->assert('Sanitize an array.')
            ->when($res = TestedClass::sanitize(['test', 'test2']))
                ->string($res)
            ->assert('Sanitize a numeric.')
            ->when($res = TestedClass::sanitize(1))
                ->string($res)->isNotEqualTo(TestedClass::sanitize('1'))
            ->assert('Sanitize a boolean.')
            ->when($res = TestedClass::sanitize(true))
                ->string($res)->isNotEqualTo(TestedClass::sanitize(1))
            ->assert('Sanitize an object.')
            ->when($res = TestedClass::sanitize(new \StdClass()))
                ->string($res)->isNotEqualTo(TestedClass::sanitize(1))
            ;
    }
}
