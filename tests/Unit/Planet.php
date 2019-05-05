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

/**
 * Class Planet
 *
 * @package NMarniesse\Phindexer\Test\Unit
 * @author Nicolas Marniesse <nicolas.marniesse@gmail.com>
 */
class Planet
{
    /**
     * @var int
     */
    private $id;

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
     * @param int $id
     * @param string $name
     * @param string $system
     * @param float $period_in_days
     */
    public function __construct(int $id, string $name, string $system, float $period_in_days)
    {
        $this->id             = $id;
        $this->name           = $name;
        $this->system         = $system;
        $this->period_in_days = $period_in_days;
    }

    /**
     * getId
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * getName
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * getSystem
     *
     * @return string
     */
    public function getSystem(): string
    {
        return $this->system;
    }

    /**
     * getPeriodInDays
     *
     * @return float
     */
    public function getPeriodInDays(): float
    {
        return $this->period_in_days;
    }
}
