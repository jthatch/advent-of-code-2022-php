<?php

declare(strict_types=1);

namespace App\Days;

use App\Contracts\Day;
use Illuminate\Support\Collection;

class Day13 extends Day
{
    public const EXAMPLE1 = <<<eof
    [1,1,3,1,1]
    [1,1,5,1,1]

    [[1],[2,3,4]]
    [[1],4]

    [9]
    [[8,7,6]]

    [[4,4],4,4]
    [[4,4],4,4,4]

    [7,7,7,7]
    [7,7,7]

    []
    [3]

    [[[]]]
    [[]]

    [1,[2,[3,[4,[5,6,7]]]],8,9]
    [1,[2,[3,[4,[5,6,0]]]],8,9]
    eof;

    /**
     * Solve Part 1 of the day's problem.
     */
    public function solvePart1(mixed $input): int|string|null
    {
        return $this->parseInput($input)
            // compare each pair and return the comparison result
            ->map(fn (array $pair): int => $this->compare($pair[0], $pair[1]))
            // filter for the pairs that are in the right order
            ->filter(fn (int $comparison): bool => -1 === $comparison)
            // get the keys of the pairs that are in the right order
            ->keys()
            // map the keys to their position in the array + 1 (since we want 1-indexed positions)
            ->map(fn (int $key): int => $key + 1)
            // sum the keys
            ->sum();
    }

    /**
     * Solve Part 2 of the day's problem.
     */
    public function solvePart2(mixed $input): int|string|null
    {
        return $this->parseInput($input)
            // flattern the array so it's no longer in pairs
            ->flatten(1)
            // add the divider packets
            ->push([[2]], [[6]])
            // sort the packets using the compare function
            ->sort(fn (array $left, array $right): int => $this->compare($left, $right))
            ->values()
            // filter for the divider packets
            ->filter(fn (array $item): bool => $item === [[2]] || $item === [[6]])
            // get the keys of the divider packets
            ->keys()
            // map the keys to their position in the array + 1 (since we want 1-indexed positions)
            ->map(fn (int $key): int => $key + 1)
            // reduce the keys to a single value by multiplying them together
            ->reduce(fn (int $carry, int $item): int => $carry * $item, 1);
    }

    /**
     * Recursively compare two arrays and returns 1, -1 or 0 following the <=> spaceship operator rules.
     * Using the following rules:
     * - If both values are integers, compare them directly.
     * - If both values are arrays, recursively compare each element.
     * - If one value is an integer and the other is an array, convert the integer to an array and compare each element.
     * @return int -1 if the left array is less than the right array, 1 if the left array is greater than the right array, 0 if they are equal.
     */
    protected function compare(array $left, array $right): int
    {
        while (true) {
            if (empty($left) && empty($right)) {
                return 0;
            }
            if (empty($left)) {
                return -1;
            }
            if (empty($right)) {
                return 1;
            }
            $leftItem  = array_shift($left);
            $rightItem = array_shift($right);

            // compare two integers, if they are not equal, return the comparison result
            if (is_int($leftItem) && is_int($rightItem)) {
                if (0 !== ($comparison = $leftItem <=> $rightItem)) {
                    return $comparison;
                }
            } elseif (is_array($leftItem) && is_array($rightItem)) {
                // compare two arrays, if they are not equal, return the comparison result
                if (0 !== ($comparison = $this->compare($leftItem, $rightItem))) {
                    return $comparison;
                }
            } else {
                // compare an integer to an array
                if (is_int($leftItem)) {
                    $leftItem = [$leftItem];
                } elseif (is_int($rightItem)) {
                    $rightItem = [$rightItem];
                }
                // compare the two arrays, if they are not equal, return the comparison result
                if (0 !== ($comparison = $this->compare($leftItem, $rightItem))) {
                    return $comparison;
                }
            }
        }
    }

    /**
     * Parse the input data.
     */
    protected function parseInput(mixed $input): Collection
    {
        $input = is_array($input) ? $input : explode("\n", $input);

        return collect($input)
            ->map(fn ($line) => mb_trim($line))
            ->chunkWhile(fn ($value) => '' !== $value)
            ->map(fn ($chunk) => $chunk->filter(fn ($value) => '' !== $value))
            ->map(fn ($chunk) => $chunk->map(fn ($line) => json_decode($line, true))->values()->toArray())
            ->values();
    }
}
