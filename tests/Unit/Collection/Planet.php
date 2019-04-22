<?php
/*
 * This file is part of PhpStorm package.
 *
 * (c) 2019 Nicolas Marniesse
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NMarniesse\Phindexer\Test\Unit\Collection;

/**
 * Class Planet
 *
 * @package NMarniesse\Phindexer\Test\Unit\Collection
 * @author Nicolas Marniesse <nicolas.marniesse@gmail.com>
 */
class Planet
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $system;

    /**
     * @var float
     */
    private $period_in_days;

    /**
     * Planet constructor.
     * @param string $name
     * @param string $system
     * @param float  $period_in_days
     */
    public function __construct(string $name, string $system, float $period_in_days)
    {
        $this->name = $name;
        $this->system = $system;
        $this->period_in_days = $period_in_days;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getSystem(): string
    {
        return $this->system;
    }

    /**
     * @return float
     */
    public function getPeriodInDays(): float
    {
        return $this->period_in_days;
    }
}
