<?php

declare(strict_types=1);

namespace App\Contracts;

use Illuminate\Support\Collection;

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

    /**
     * Override if there's a second example.
     */
    public function getExample2(): mixed
    {
        return static::EXAMPLE1;
    }

    /**
     * Override to customise input parsing.
     */
    protected function parseInput(mixed $input): Collection
    {
        $input = is_array($input) ? $input : explode("\n", $input);

        return collect($input);
    }

    /**
     * Returns the day we are on.
     */
    final public function day(): string
    {
        return (new \ReflectionClass($this))->getShortName();
    }
}
