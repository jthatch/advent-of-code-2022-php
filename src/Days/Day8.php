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

                // A tree is visible if all of the other trees between it and an edge of the grid are shorter than it.
                // Only consider trees in the same row or column; that is, only look up, down, left, or right from any given tree.
                if (!empty(array_filter(
                    $adjacent,
                    function (array $pos) use ($y, $x, $input, $location): bool {
                        $newY        = $y + $pos[0];
                        $newX        = $x + $pos[1];
                        $locAdjacent = $input[$y + $pos[0]][$x + $pos[1]] ?? null;
                        if (null === $locAdjacent) {
                            return true;
                        }
                        // todo solve this
                        printf("y: %s x: %s ay: %s ax: %s\n", $y, $x, $y + $pos[0], $x + $pos[1]);
                        // 22 but needs to be 21
                        /**
                         * 2^ array:22 [
                         * "0-0" => "3"
                         * "0-1" => "0"
                         * "0-2" => "3"
                         * "0-3" => "7"
                         * "0-4" => "3"
                         * "1-0" => "2"
                         *
                         * "1-1" => "5"
                         * "1-2" => "5"

                         * "1-4" => "2"
                         *
                         * "2-0" => "6"
                         *
                         * "2-1" => "5"
                         * "2-3" => "3"
                         * "2-4" => "2"
                         * "3-0" => "3"
                         * "3-2" => "5"
                         * "3-3" => "4"
                         * "3-4" => "9"
                         * "4-0" => "3"
                         * "4-1" => "5"
                         * "4-2" => "3"
                         * "4-3" => "9"
                         * "4-4" => "0"
                         * ]
 */
                        return $location > $locAdjacent;
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
