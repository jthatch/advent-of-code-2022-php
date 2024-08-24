<?php

declare(strict_types=1);

namespace App\Days;

use App\Contracts\Day;
use Illuminate\Support\Collection;

class Day3 extends Day
{
    public const EXAMPLE1 = <<<eof
        vJrwpWtwJgWrhcsFMMfFFhFp
        jqHRNqRjqzjGDLGLrsFMfFZSrLrFZsSL
        PmmdzqPrVvPwwTWBwg
        wMqvLMZHhHMvwLHjbvcjnnSBnvTQFn
        ttgJtRGJQctTZtZT
        CrZsJsPPZsGzwwsLwLmpwMDw
        eof;

    /**
     * Find the item type that appears in both compartments of each rucksack. What is the sum of the priorities of those item types?
     */
    public function solvePart1(mixed $input): int|string|null
    {
        //  given rucksack always has the same number of items in each of its two compartments,
        // so the first half of the characters represent items in the first compartment,
        // while the second half of the characters represent items in the second compartment.
        return $this->parseInput($input)
            // split the characters in half for each compartment in the rucksack
            ->map(function (string $line) {
                $halfLength = intdiv(strlen($line), 2);

                return [
                    substr($line, 0, $halfLength),
                    substr($line, $halfLength),
                ];
            })
            // find common characters
            ->map(function (array $parts) {
                [$part1, $part2] = $parts;
                $commonChars     = array_intersect(
                    str_split($part1),
                    str_split($part2)
                );

                return array_unique($commonChars);
            })
            // convert to a priority
            ->map(
                fn (array $commonChars) => collect($commonChars)
                    ->map(
                        fn ($char) => ctype_lower($char)
                            ? ord($char) - ord('a') + 1 // a-z: 1-26
                            : ord($char) - ord('A') + 27 // A-Z: 27-52
                    )->toArray()
            )
            // sum them all
            ->map(fn ($chunk) => array_sum($chunk))->sum();
    }

    public function solvePart2(mixed $input): int|string|null
    {
        return $this->parseInput($input)
            // split into groups of 3
            ->chunk(3)
            // identify the badge: the badge is the only item type carried by all three Elves.
            ->map(function (Collection $chunk) {
                [$first, $second, $third] = $chunk->values();

                return array_unique(array_intersect(
                    str_split($first),
                    str_split($second),
                    str_split($third),
                ));
            })
            // convert to a priority
            ->map(
                fn (array $commonChars) => collect($commonChars)
                    ->map(
                        fn ($char) => ctype_lower($char)
                            ? ord($char) - ord('a') + 1 // a-z: 1-26
                            : ord($char) - ord('A') + 27 // A-Z: 27-52
                    )->toArray()
            )
            // sum them all
            ->map(fn ($chunk) => array_sum($chunk))->sum();
    }

    protected function parseInput(mixed $input): Collection
    {
        $input = is_array($input) ? $input : explode("\n", $input);

        return collect($input);
    }

    public function getExample2(): mixed
    {
        return static::EXAMPLE1;
    }
}
