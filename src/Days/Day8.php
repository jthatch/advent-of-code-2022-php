<?php

declare(strict_types=1);

namespace App\Days;

use App\Contracts\Day;
use Illuminate\Support\Collection;
use Generator;

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

    /**
     * Consider your map; how many trees are visible from outside the grid?
     */
    public function solvePart1(mixed $input): int|string|null
    {
        $input = $this
            ->parseInput($input)
            ->toArray();

        $visible = [];

        foreach ($this->treeIterator($input) as [$key, $y, $x, $yMax, $xMax, $height, $isEdge]) {
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
                        $y += (int) $pos[0];
                        $x += (int) $pos[1];
                        // applying null if we exceed the boundaries (go past the edge)
                        $nextHeight = $input[$y][$x] ?? null;

                        // break when we hit the edge
                        if (null === $nextHeight) {
                            break;
                        }

                        // we've hit a tree taller than ourselves, therefore we aren't visible
                        if ($height <= $nextHeight) {
                            $isVisible = false;
                            break;
                        }

                        // otherwise we are still the tallest tree
                        $isVisible = true;
                    }
                });

            // ultimately if we are visible record it
            if ($isVisible) {
                $visible[$key] = $height;
            }
        }

        return count($visible);
    }

    /**
     * Consider each tree on your map. What is the highest scenic score possible for any tree?
     */
    public function solvePart2(mixed $input): int|string|null
    {
        $input = $this
            ->parseInput($input)
            ->toArray();

        $scenicScores = [];

        foreach ($this->treeIterator($input) as [$key, $y, $x, $yMax, $xMax, $height, $isEdge]) {
            // the number of trees we can see over
            $visibility = [];
            collect($this->adjacent)
                ->each(function (array $pos) use ($y, $x, $height, $input, &$visibility): void {
                    $visible = 0;
                    while (true) {
                        // keep travelling in the adjacent direction
                        $y += (int) $pos[0];
                        $x += (int) $pos[1];
                        $nextHeight = $input[$y][$x] ?? null;

                        // break when we hit the edge
                        if (null === $nextHeight) {
                            break;
                        }

                        ++$visible;

                        // we've hit a tree taller than ourselves, therefore we aren't visible
                        if ($height <= $nextHeight) {
                            break;
                        }
                    }
                    $visibility[] = $visible;
                });
            // multiply the values together
            $scenicScores[$key] = array_reduce($visibility, fn ($carry, $item) => $carry * $item, 1);
        }

        return collect($scenicScores)->max();
    }

    /**
     * Working with coordinates isn't really collect()'s strong suit, so did this the old-fashioned way.
     *
     * @param array $input array<string, int> $input key in format `y-x`
     *
     * @return Generator array<string, int, int, int, int, int, bool>
     */
    protected function treeIterator(array $input): Generator
    {
        // loop over our input in y,x coordinates
        for ($y = 0, $yMax = count($input); $y < $yMax; ++$y) {
            for ($x = 0, $xMax = count($input[0]); $x < $xMax; ++$x) {
                $isEdge = 0 === $y || 0 === $x || $y === ($yMax - 1) || $x === ($xMax - 1);
                $key    = sprintf('%s-%s', $y, $x);
                $height = $input[$y][$x];
                yield [$key, $y, $x, $yMax, $xMax, $height, $isEdge];
            }
        }
    }

    protected function parseInput(mixed $input): Collection
    {
        $input = is_array($input) ? $input : explode("\n", $input);

        return collect($input)
            ->map(fn (string $line) => mb_str_split($line));
    }
}
