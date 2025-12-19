<?php

declare(strict_types=1);

namespace App\Days;

use App\Contracts\Day;
use Illuminate\Support\Collection;

class Day4 extends Day
{
    public const EXAMPLE1 = <<<eof
        2-4,6-8
        2-3,4-5
        5-7,7-9
        2-8,3-7
        6-6,4-6
        2-6,4-8
        eof;

    /**
     * In how many assignment pairs does one range fully contain the other?
     */
    public function solvePart1(mixed $input): int|string|null
    {
        return $this->parseInput($input)
            // compare both pairs e.g. [2,4] and [1,8] and determine if one pair fully contains the other
            ->filter(fn (array $pair) => (
                ($pair[0][0] <= $pair[1][0] && $pair[0][1] >= $pair[1][1]) || ($pair[1][0] <= $pair[0][0] && $pair[1][1] >= $pair[0][1])
            ))
            ->count();
    }

    /**
     * In how many assignment pairs do the ranges overlap?
     */
    public function solvePart2(mixed $input): int|string|null
    {
        return $this->parseInput($input)
            // compare both pairs e.g. [5,7] and [7,9] and determine if one pair overlaps another at all
            ->filter(fn (array $pair) => (
                ($pair[0][0] <= $pair[1][1] && $pair[0][1] >= $pair[1][0]) || ($pair[1][0] <= $pair[0][1] && $pair[1][1] >= $pair[0][0])
            ))
            ->count();
    }

    protected function parseInput(mixed $input): Collection
    {
        $input = is_array($input) ? $input : explode("\n", $input);

        return collect($input)
            // split into pairs
            ->map(fn (string $line) => explode(',', $line))
            // extract the first and last range 2-8 [2,8] for each pair
            ->map(fn (array $pair) => array_map(fn (string $range) => array_map('intval', explode('-', $range)), $pair));
    }
}
