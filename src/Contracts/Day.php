<?php

declare(strict_types=1);

namespace App\Contracts;

use Illuminate\Support\Collection;
use ReflectionClass;

abstract class Day implements DayInterface
{
    /** @var string|array EXAMPLE1 */
    public const EXAMPLE1 = '';
    /** @var string|array|null EXAMPLE2 there's not always a second example so this can be null */
    public const EXAMPLE2 = null;

    protected mixed $longRunningCallback = null; // Closure

    /**
     * @param array<int, string> $input
     */
    public function __construct(public readonly mixed $input)
    {
    }

    abstract public function solvePart1(mixed $input): int|string|null;

    abstract public function solvePart2(mixed $input): int|string|null;

    public function getExample1(): string|array
    {
        return static::EXAMPLE1;
    }

    /**
     * If there's a second example, return that, otherwise return the first example.
     */
    public function getExample2(): string|array
    {
        return static::EXAMPLE2 ?? static::EXAMPLE1;
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
        return (new ReflectionClass($this))->getShortName();
    }

    /**
     * Sets a callback to report memory usage on long running operations.
     */
    public function setLongRunningCallback(callable $callback): self
    {
        $this->longRunningCallback = $callback;

        return $this;
    }

    /**
     * Reports memory usage on long running operations.
     */
    protected function reportLongRunning(): void
    {
        if (null !== $this->longRunningCallback) {
            ($this->longRunningCallback)();
        }
    }
}
