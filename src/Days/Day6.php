<?php

declare(strict_types=1);

namespace App\Days;

use App\Contracts\Day;
use Illuminate\Support\Collection;

class Day6 extends Day
{
    public const EXAMPLE1 = <<<eof
        mjqjpqmgbljsphdztnvjfqwrcgsmlb
        eof;

    /**
     * How many characters need to be processed before the first start-of-packet marker is detected?
     */
    public function solvePart1(mixed $input): int|string|null
    {
        return $this->processBuffer($this->parseInput($input), 4);
    }

    /**
     * How many characters need to be processed before the first start-of-message marker is detected?
     */
    public function solvePart2(mixed $input): int|string|null
    {
        return $this->processBuffer($this->parseInput($input), 14);
    }

    protected function processBuffer(Collection $input, int $distinctCount): int
    {
        $uniqueValues = collect();
        $offset       = 1;

        $input->each(function (string $character, int $index) use ($uniqueValues, $distinctCount, &$offset) {
            // add character to our set but only keep the last 4
            $uniqueValues->push($character);
            if ($uniqueValues->count() > $distinctCount) {
                $uniqueValues->shift();
            }

            if ($distinctCount === $uniqueValues->unique()->count()) {
                // return the index incremented by 1, returning false terminates the loop early
                $offset = ++$index;

                return false;
            }

            return true;
        });

        return $offset;
    }

    protected function parseInput(mixed $input): Collection
    {
        $input = is_array($input) ? $input : explode("\n", $input);

        return collect($input)->flatMap(fn ($line) => mb_str_split($line));
    }
}
