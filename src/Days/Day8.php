<?php

declare(strict_types=1);

namespace App\Days;

use App\Contracts\Day;
use Illuminate\Support\Collection;

class Day8 extends Day
{
    public const EXAMPLE1 = <<<eof
        30373
        25512
        65332
        33549
        35390
        eof;

    public function solvePart1(mixed $input): int|string|null
    {
        $input = $this
            ->parseInput($input)
            ->toArray();
        $adjacent = [[-1, 0], [0, 1], [1, 0], [0, -1]];

        $visible = [];

        for ($y = 0, $yMax = count($input); $y < $yMax; ++$y) {
            for ($x = 0, $xMax = count($input[0]); $x < $xMax; ++$x) {
                $location = $input[$y][$x];
                $key      = sprintf('%s-%s', $y, $x);
                printf("key: %s location: %s\n", $key, $location);

                // loop over our adjacent positions, if none are bigger then we've found a visible tree
                if (!empty(array_filter(
                    $adjacent,
                    function (array $pos) use ($y, $x, $input, $location): bool {
                        $newY        = $y + $pos[0];
                        $newX        = $x + $pos[1];
                        $locAdjacent = $input[$y + $pos[0]][$x + $pos[1]] ?? null;
                        // todo solve this
                        printf("y: %s x: %s ay: %s ax: %s\n", $y, $x, $y + $pos[0], $x + $pos[1]);

                        return null !== $locAdjacent && $location >= $locAdjacent;
                    }
                ))) {
                    $key           = sprintf('%s-%s', $y, $x);
                    $visible[$key] = $location;
                }
            }
        }
        dd('visible', $visible);

        return ''; // should be 21
    }

    public function solvePart2(mixed $input): int|string|null
    {
        $input = $this->parseInput($input);

        return '';
    }

    protected function parseInput(mixed $input): Collection
    {
        $input = is_array($input) ? $input : explode("\n", $input);

        return collect($input)
            ->map(fn (string $line) => str_split($line));
    }
}
