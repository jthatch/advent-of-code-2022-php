<?php

declare(strict_types=1);

namespace App\Contracts;

abstract class Day implements DayInterface
{
    public const EXAMPLE1 = '';
    public const EXAMPLE2 = '';

    /**
     * @param array<int, string> $input
     */
    public function __construct(public readonly mixed $input)
    {
    }

    abstract public function solvePart1(mixed $input): int|string|null;

    abstract public function solvePart2(mixed $input): int|string|null;

    public function getExample1(): mixed
    {
        return static::EXAMPLE1;
    }

    public function getExample2(): mixed
    {
        return static::EXAMPLE2;
    }

    /**
     * Returns the day we are on.
     */
    final public function day(): string
    {
        return (new \ReflectionClass($this))->getShortName();
    }
}
