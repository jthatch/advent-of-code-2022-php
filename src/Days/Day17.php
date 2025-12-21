<?php

declare(strict_types=1);

namespace App\Days;

use App\Contracts\Day;
use Illuminate\Support\Collection;

class Day17 extends Day
{
    public const EXAMPLE1 = <<<EOF
    >>><<><>><<<>><>>><<<>>><<<><<<>><>><<>>
    EOF;

    /** @var array|array[] ROCKS these are stored in [y][x] order from top to bottom */
    protected const array ROCKS = [
        [['#','#','#','#']],                            // -
        [['.','#','.'], ['#','#','#'], ['.','#','.']],  // +
        [['.','.','#'], ['.','.','#'], ['#','#','#']],  // backwards L
        [['#'],['#'], ['#'], ['#']],                    // |
        [['#','#'], ['#','#']]                          // square
    ];
    protected const int NO_INTERACTIVE    = 0;
    protected const int INTERACTIVE_DELAY = 2;

    protected int $interactiveModePart1 = self::NO_INTERACTIVE;
    protected int $interactiveModePart2 = self::NO_INTERACTIVE;

    protected array $settledRocks = [];  // permanent grid of settled rocks
    protected int $highestPoint   = 0;     // track highest settled rock
    protected int $jetIndex       = 0;         // track jet pattern position across all rocks

    public function solvePart1(mixed $input): int|string|null
    {
        $input              = $this->parseInput($input);
        $this->settledRocks = [];
        $this->highestPoint = 0;
        $this->jetIndex     = 0;

        $rockTypes  = count(static::ROCKS);
        $totalRocks = 2022;

        // simulate 2022 rocks falling, cycling through rock types
        for ($i = 0; $i < $totalRocks; $i++) {
            $rock = static::ROCKS[$i % $rockTypes];
            $this->simulateRock($rock, $i, $input);
        }

        return $this->highestPoint;
    }

    protected function simulateRock(array $rock, int $rockIndex, Collection $jetPattern): void
    {
        $rockHeight = count($rock);
        $rockWidth  = count($rock[0]);

        // starting position: 2 from left, 3 above highest settled rock
        $rockX = 2;
        $rockY = $this->highestPoint + 3;

        $settled = false;
        while (!$settled) {
            // render current state
            if (self::INTERACTIVE_DELAY === $this->interactiveModePart1) {
                $this->renderGridWithRock($rockX, $rockY, $rock);
                usleep(100_000);
            }

            // apply jet push (left/right) - use global jet index that persists across rocks
            $jet = $jetPattern->get($this->jetIndex % $jetPattern->count());
            $this->jetIndex++;

            $newX = '<' === $jet ? $rockX - 1 : $rockX + 1;

            // check if horizontal move is valid
            if ($this->canPlaceRock($rock, $newX, $rockY)) {
                $rockX = $newX;
            }

            // move down
            $newY = $rockY - 1;

            // check if can move down
            if ($this->canPlaceRock($rock, $rockX, $newY)) {
                $rockY = $newY;
            } else {
                // can't move down, rock has settled
                $settled = true;
                $this->mergeRockIntoGrid($rockX, $rockY, $rock);
            }
        }
    }

    protected function canPlaceRock(array $rock, int $x, int $y): bool
    {
        $rockHeight = count($rock);

        foreach ($rock as $dy => $row) {
            foreach ($row as $dx => $cell) {
                if ('#' === $cell) {
                    $checkX = $x + $dx;
                    // rock coordinates are top-down, but y increases upward, so invert dy
                    $checkY = $y + ($rockHeight - 1 - $dy);

                    // check boundaries (floor is at y=0)
                    if ($checkX < 0 || $checkX >= 7 || $checkY < 0) {
                        return false;
                    }

                    // check collision with settled rocks
                    if (isset($this->settledRocks[$checkY][$checkX]) && '#' === $this->settledRocks[$checkY][$checkX]) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    protected function mergeRockIntoGrid(int $x, int $y, array $rock): void
    {
        $rockHeight = count($rock);

        foreach ($rock as $dy => $row) {
            foreach ($row as $dx => $cell) {
                if ('#' === $cell) {
                    $finalX = $x + $dx;
                    // rock coordinates are top-down, but y increases upward, so invert dy
                    $finalY = $y + ($rockHeight - 1 - $dy);

                    $this->settledRocks[$finalY][$finalX] = '#';

                    // update highest point (highest y coordinate + 1)
                    $this->highestPoint = max($this->highestPoint, $finalY + 1);
                }
            }
        }
    }

    protected function renderGridWithRock(int $rockX, int $rockY, array $rock): void
    {
        if (!$this->interactiveModePart1) {
            return;
        }

        $rockHeight = count($rock);

        // create display grid from settled rocks + current falling rock
        $displayHeight = max($this->highestPoint + 10, $rockY + $rockHeight + 5);
        $grid          = array_fill(0, $displayHeight, array_fill(0, 7, '.'));

        // place settled rocks
        foreach ($this->settledRocks as $y => $row) {
            foreach ($row as $x => $cell) {
                if ('#' === $cell) {
                    $grid[$y][$x] = '#';
                }
            }
        }

        // overlay current falling rock (invert rock coordinates to match upward y)
        foreach ($rock as $dy => $row) {
            foreach ($row as $dx => $cell) {
                if ('#' === $cell) {
                    $displayY = $rockY + ($rockHeight - 1 - $dy);
                    if ($displayY >= 0 && $displayY < $displayHeight) {
                        $grid[$displayY][$rockX + $dx] = '@';
                    }
                }
            }
        }

        // render from top down (reverse order so floor is at bottom)
        $grid = array_reverse($grid);

        // clear screen
        echo "\e[2J\e[H";

        $this->renderGrid($grid);
        printf("Height: %d | Rock Y: %d\n", $this->highestPoint, $rockY);
    }

    protected function renderGrid(array $grid, int $width = 7): void
    {
        if (!$this->interactiveModePart1) {
            return;
        }

        foreach ($grid as $row) {
            $coloredRow = implode('', array_map(
                fn (string $cell) => match ($cell) {
                    '#'     => "\e[1;37m#\e[0m",      // bold white for settled rocks
                    '@'     => "\e[1;96m@\e[0m",      // bright cyan for falling rock
                    default => "\e[2;37m.\e[0m",  // dim gray for empty cells
                },
                $row
            ));
            printf("\e[93m|\e[0m%s\e[93m|\e[0m\n", $coloredRow);
        }
        // print the floor in light yellow
        printf("\e[93m+%s+\e[0m\n", str_repeat('-', $width));
    }

    public function solvePart2(mixed $input): int|string|null
    {
        $input = $this->parseInput($input);

        // todo: implement solution for Part 2

        return null;
    }

    protected function parseInput(mixed $input): Collection
    {
        $input = is_array($input) ? $input : explode("\n", $input);

        // convert instructions into an array
        return collect(mb_str_split($input[0]));
    }
}
