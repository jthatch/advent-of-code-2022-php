<?php

declare(strict_types=1);

namespace App\Days;

use App\Contracts\Day;
use Illuminate\Support\Collection;

class Day1 extends Day
{
    public const EXAMPLE1 = <<<eof
        1000
        2000
        3000
        
        4000
        
        5000
        6000
        
        7000
        8000
        9000
        
        10000
        eof;

    /**
     * Find the Elf carrying the most Calories. How many total Calories is that Elf carrying?
     */
    public function solvePart1(mixed $input): int|string|null
    {
        $input = $this->parseInput($input);

        return $input->map(fn ($chunk) => $chunk->sum())
            ->max();
    }

    /**
     * Find the top three Elves carrying the most Calories. How many Calories are those Elves carrying in total?
     */
    public function solvePart2(mixed $input): int|string|null
    {
        $input = $this->parseInput($input);

        return $input->map(fn ($chunk) => $chunk->sum())
            ->sortDesc()
            ->take(3)
            ->sum();
    }

    protected function parseInput(mixed $input): Collection
    {
        $input = is_array($input) ? $input : explode("\n", $input);

        return collect($input)
            ->map(fn ($line) => trim($line))
            ->chunkWhile(fn ($value) => '' !== $value)
            ->map(fn ($chunk) => $chunk->values())
            ->map(fn ($chunk) => $chunk->filter(fn ($value) => '' !== $value)->values())
            ->map(fn ($chunk) => $chunk->values()->map(fn ($value) => (int) $value))
            ->values();
    }

    public function getExample2(): mixed
    {
        return static::EXAMPLE1;
    }
}
