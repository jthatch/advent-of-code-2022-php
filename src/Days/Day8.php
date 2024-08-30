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

    // [[y,x],..] each of the 4 possible adjacent locations, starting from top going clockwise
    private array $adjacent = [[-1, 0], [0, 1], [1, 0], [0, -1]];

    public function solvePart1(mixed $input): int|string|null
    {
        $input = $this
            ->parseInput($input)
            ->toArray();

        $visible = [];

        // loop over our input in y,x coordinates
        for ($y = 0, $yMax = count($input); $y < $yMax; ++$y) {
            for ($x = 0, $xMax = count($input[0]); $x < $xMax; ++$x) {
                $isEdge = 0 === $y || 0 === $x || $y === ($yMax - 1) || $x === ($xMax - 1);
                $key    = sprintf('%s-%s', $y, $x);
                $height = $input[$y][$x];

                // if our tree is on the edge the rules are it's always visible so handle this now
                if ($isEdge) {
                    $visible[$key] = $height;
                    continue;
                }

                // otherwise, from our starting location traverse in each direction until we hit a the edge
                // a tree is visible if it's the tallest in at least one direction
                $isVisible = false;
                collect($this->adjacent)
                    ->each(function (array $pos) use ($y, $x, $height, $input, &$isVisible) {
                        // tree is already deemed visible so we can break out of further adjacent loops
                        if ($isVisible) {
                            return true;
                        }

                        while (true) {
                            // keep travelling in the adjacent direction
                            $y += $pos[0];
                            $x += $pos[1];
                            $nextHeight = $input[$y][$x] ?? null;

                            if (null === $nextHeight) {
                                break;
                            }

                            if ($height <= $nextHeight) {
                                $isVisible = false;
                                break;
                            }

                            $isVisible = true;
                        }
                    });

                if ($isVisible) {
                    $visible[$key] = $height;
                }
            }
        }

        return count($visible);
    }

    /**
     * Working with coordinates isn't really collect()'s strong suit, so did this the old-fashioned way.
     *
     * @return array<string, int> $input key in format `y-x`
     */
    protected function getLowPoints(array $heightmap): array
    {
        $lowPoints = [];
        for ($y = 0, $yMax = count($heightmap); $y < $yMax; ++$y) {
            for ($x = 0, $xMax = count($heightmap[0]); $x < $xMax; ++$x) {
                $height = $heightmap[$y][$x];

                // loop over our adjacent positions, if none are bigger then we've found a low point
                if (empty(array_filter(
                    $this->adjacent,
                    function (array $pos) use ($height, $heightmap, $y, $x, $yMax, $xMax): bool {
                        $adjacent = $heightmap[$y + $pos[0]][$x + $pos[1]] ?? -1;
                        $isEdge   = 0 === $y || 0 === $x || $y === ($yMax - 1) || $x === ($xMax - 1);

                        return $isEdge || $height > $adjacent;
                    }
                ))) {
                    $key             = sprintf('%s-%s', $y, $x);
                    $lowPoints[$key] = $height;
                }
            }
        }

        return $lowPoints;
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
