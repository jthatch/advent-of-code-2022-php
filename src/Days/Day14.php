<?php

declare(strict_types=1);

namespace App\Days;

use App\Contracts\Day;
use Illuminate\Support\Collection;

class Day14 extends Day
{
    public const EXAMPLE1 = <<<eof
    498,4 -> 498,6 -> 496,6
    503,4 -> 502,4 -> 502,9 -> 494,9
    eof;

    /**
     * Solve Part 1 of the day's problem.
     */
    public function solvePart1(mixed $input): int|string|null
    {
        $input = $this->parseInput($input);

        // todo wip
        dd($input);

        return null;
    }

    /**
     * Solve Part 2 of the day's problem.
     */
    public function solvePart2(mixed $input): int|string|null
    {
        $input = $this->parseInput($input);

        // todo: implement solution for Part 2

        return null;
    }

    /**
     * Parse the input data.
     */
    protected function parseInput(mixed $input): Collection
    {
        $input = is_array($input) ? $input : explode("\n", $input);

        return collect($input)
            ->map(fn (string $line) => explode(' -> ', $line))
            ->map(
                fn (array $points) => collect($points)
                    ->map(fn (string $point) => array_map('intval', explode(',', $point)))
                    ->toArray()
            )
        ;
    }
}
