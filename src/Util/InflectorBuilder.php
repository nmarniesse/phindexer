<?php
/*
 * This file is part of PhpStorm package.
 *
 * (c) 2019 Nicolas Marniesse
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace NMarniesse\Phindexer\Util;

use Doctrine\Common\Inflector\Inflector;

/**
 * Class InflectorBuilder
 *
 * @package NMarniesse\Phindexer\Util
 * @author Nicolas Marniesse <nicolas.marniesse@gmail.com>
 */
class InflectorBuilder
{
    public static function build(): Inflector
    {
        return new Inflector();
    }
}
