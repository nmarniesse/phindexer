<?php
/*
 * This file is part of phindexer package.
 *
 * (c) 2018 Nicolas Marniesse <nicolas.marniesse@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace NMarniesse\Phindexer\Util;

/**
 * Class IndexSanitizer
 *
 * @package NMarniesse\Phindexer
 * @author  Nicolas Marniesse <nicolas.marniesse@phc-holding.com>
 */
class IndexSanitizer
{
    /**
     * sanitize
     *
     * @param mixed $value
     * @return string
     */
    public static function sanitize($value): string
    {
        if (is_string($value)) {
            return sprintf('_string_%s', $value);
        }

        if (is_numeric($value)) {
            return sprintf('_numeric_%s', $value);
        }

        if (is_bool($value)) {
            return sprintf('_bool_%d', $value ? 1 : 0);
        }

        if (is_array($value)) {
            return sprintf('_array_%s', json_encode($value));
        }

        return sprintf('_object_%s', json_encode($value));
    }
}
