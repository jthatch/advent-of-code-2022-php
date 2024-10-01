<?php

declare(strict_types=1);

namespace App\Contracts;

use Illuminate\Support\Collection;
use ReflectionClass;

abstract class Day implements DayInterface
{
    /** string|array EXAMPLE1 */
    public const EXAMPLE1 = '';
    /** string|array EXAMPLE2 */
    public const EXAMPLE2 = '';

    /** @var callable|null a callback to report memory usage on long running operations */
    protected $longRunningCallback = null;

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
        return (new ReflectionClass($this))->getShortName();
    }

    /**
     * sets a callback to report memory usage on long running operations
     * @param callable $callback
     * @return $this
     */
    public function setLongRunningCallback(callable $callback): self
    {
        $this->longRunningCallback = $callback;

        return $this;
    }

    /**
     * reports memory usage on long running operations
     */
    protected function reportLongRunning(): void
    {
        if ($this->longRunningCallback && is_callable($this->longRunningCallback)) {
            ($this->longRunningCallback)();
        }
    }
}
