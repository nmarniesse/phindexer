<?php
/*
 * This file is part of phindexer package.
 *
 * (c) 2018 Nicolas Marniesse <nicolas.marniesse@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NMarniesse\Phindexer\Validator;

use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class ValidatorFactory
 *
 * @package NMarniesse\Phindexer\Validator
 * @author  Nicolas Marniesse <nicolas.marniesse@gmail.com>
 */
class ValidatorFactory
{
    /**
     * @return ValidatorInterface
     */
    public static function getValidator(): ValidatorInterface
    {
        return Validation::createValidator();
    }
}
