<?php

namespace App\Contracts;

abstract class Day implements DayInterface
{
    abstract public function solvePart1(mixed $input): ?string;

    abstract public function solvePart2(mixed $input): ?string;

    /**
     * Override this method to perform parsing on the days input
     * @param array $input
     * @return mixed
     */
    public function parseInput(array $input): mixed
    {
        return $input;
    }

    /**
     * Returns the day we are on
     *
     * @return string
     */
    final public function day(): string
    {
        return (new \ReflectionClass($this))->getShortName();
    }

}