<?php

declare(strict_types=1);

namespace App\Days;

use App\Contracts\Day;
use Illuminate\Support\Collection;

class Day12 extends Day
{
    public const EXAMPLE1 = <<<EOF
        // TODO: Add example 1
    EOF;

    /**
     * Solve Part 1 of the day's problem.
     */
    public function solvePart1(mixed $input): int|string|null
    {
        $input = $this->parseInput($input);

        // TODO: Implement solution for Part 1

        return null;
    }

    /**
     * Solve Part 2 of the day's problem.
     */
    public function solvePart2(mixed $input): int|string|null
    {
        $input = $this->parseInput($input);

        // TODO: Implement solution for Part 2

        return null;
    }

    /**
     * Parse the input data.
     */
    protected function parseInput(mixed $input): Collection
    {
        $input = is_array($input) ? $input : explode("\n", $input);

        return collect($input)
            // TODO: Add any necessary transformations
        ;
    }
}
