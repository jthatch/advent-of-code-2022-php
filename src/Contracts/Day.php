<?php

declare(strict_types=1);

namespace App\Contracts;

abstract class Day implements DayInterface
{
    abstract public function solvePart1(mixed $input): ?string;

    abstract public function solvePart2(mixed $input): ?string;

    /**
     * Override this method to perform parsing on the days input.
     */
    public function parseInput(array $input): mixed
    {
        return $input;
    }

    /**
     * Returns the day we are on.
     */
    final public function day(): string
    {
        return (new \ReflectionClass($this))->getShortName();
    }
}
