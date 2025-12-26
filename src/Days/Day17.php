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

    /**
     * @var array|array[] ROCKS these are stored in [y][x] order from top to bottom
     * 'h' = height (cached for performance, count() is slow)
     * 'r' = rock shape (array of rows, each row is an array of cells)
     **/
    protected const array ROCKS = [
        ['h' => 1, 'r' => [['#','#','#','#']]],                            // -
        ['h' => 3, 'r' => [['.','#','.'], ['#','#','#'], ['.','#','.']]],  // +
        ['h' => 3, 'r' => [['.','.','#'], ['.','.','#'], ['#','#','#']]],  // backwards L
        ['h' => 4, 'r' => [['#'],['#'], ['#'], ['#']]],                    // |
        ['h' => 2, 'r' => [['#','#'], ['#','#']]]                          // square
    ];
    protected const int NO_INTERACTIVE    = 0;
    protected const int INTERACTIVE_DELAY = 2;
    protected const int KEEP_ROWS         = 1000; // number of rows to keep when pruning

    protected int $interactiveModePart1 = self::NO_INTERACTIVE;
    protected int $interactiveModePart2 = self::NO_INTERACTIVE;

    protected array $settledRocks = []; // permanent grid of settled rocks
    protected int $highestPoint   = 0;  // track highest settled rock
    protected int $jetIndex       = 0;  // track jet pattern position across all rocks
    protected int $jetCount       = 0; // count the total number of jets
    protected array $state        = [];    // store rock states

    public function solvePart1(mixed $input): int|string|null
    {
        $input              = $this->parseInput($input);
        $this->jetCount     = $input->count();
        $this->settledRocks = [];
        $this->highestPoint = 0;
        $this->jetIndex     = 0;

        $rockTypes  = count(static::ROCKS);
        $totalRocks = 2022;

        // simulate 2022 rocks falling, cycling through rock types
        for ($i = 0; $i < $totalRocks; $i++) {
            $this->simulateRock($i, $i % $rockTypes, $input, $this->interactiveModePart1);
        }

        return $this->highestPoint;
    }

    public function solvePart2(mixed $input): int|string|null
    {
        $input              = $this->parseInput($input);
        $this->jetCount     = $input->count();
        $this->settledRocks = [];
        $this->highestPoint = 0;
        $this->jetIndex     = 0;
        $this->state        = [];

        $rockTypes  = count(static::ROCKS);
        $totalRocks = 1_000_000_000_000; // actual number we need to find

        // simulate rocks falling until we find a cycle
        for ($i = 0; $i < $totalRocks; $i++) {
            $cycleData = $this->simulateRock($i, $i % $rockTypes, $input, $this->interactiveModePart2);

            if ($cycleData) {
                // we have detected a cycle, where the rock and position repeats.
                // we can now calculate the final height by multiplying the current height by the cycle length.
                $cycleStartRock   = $cycleData['rockCount'];
                $cycleStartHeight = $cycleData['height'];
                $cycleLength      = $i                  - $cycleStartRock;
                $cycleHeightGain  = $this->highestPoint - $cycleStartHeight;

                $remainingRocks = $totalRocks - ($i + 1);
                $completeCycles = intdiv($remainingRocks, $cycleLength);
                // find the remainder and simulate it below
                $rocksAfterCycles = $remainingRocks % $cycleLength;

                // fast-forward through complete cycles
                $heightAfterCycles = $this->highestPoint + ($completeCycles * $cycleHeightGain);

                // track height before simulating remainder
                $heightBeforeRemainder = $this->highestPoint;

                // simulate the remaining rocks after the cycles
                for ($j = 0; $j < $rocksAfterCycles; $j++) {
                    $rockIndex = $i + 1 + $j;
                    $this->simulateRock($rockIndex, $rockIndex % $rockTypes, $input, $this->interactiveModePart2);
                }

                // calculate height gained from simulating the remainder rocks
                $heightGainedFromRemainder = $this->highestPoint - $heightBeforeRemainder;

                return $heightAfterCycles + $heightGainedFromRemainder;
            }
        }

        return $this->highestPoint;
    }

    protected function simulateRock(int $rockCount, int $rockType, Collection $jetPattern, int $interactiveMode): ?array
    {
        $rock    = static::ROCKS[$rockType];
        $jetType = 0;
        // starting position: 2 from left, 3 above the highest settled rock
        $rockX = 2;
        $rockY = $this->highestPoint + 3;

        $settled = false;
        while (!$settled) {
            $jetType = $this->jetIndex++ % $this->jetCount;
            // render current state
            if (self::INTERACTIVE_DELAY === $interactiveMode) {
                $this->renderGridWithRock($rockX, $rockY, $rock);
                usleep(50_000);
            }

            // apply jet push (left/right)
            $newX = '<' === $jetPattern->get($jetType)
                ? $rockX - 1
                : $rockX + 1;

            // check if the horizontal move is valid
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
                $this->mergeRockIntoGrid($rockX, $rockY, $rock, self::KEEP_ROWS);
            }
        }

        // track our state for cycle detection (only after tower is tall enough)
        if ($this->highestPoint >= 20) {
            $surfaceProfile = $this->getSurfaceProfile(self::KEEP_ROWS);
            $stateKey       = sprintf('%d-%d-%s', $rockType, $jetType, implode('-', $surfaceProfile));

            if (isset($this->state[$stateKey])) {
                // cycle found! return the previous state data
                return $this->state[$stateKey];
            }

            // store current state for future cycle detection
            $this->state[$stateKey] = [
                'rockCount' => $rockCount,
                'height'    => $this->highestPoint,
            ];
        }

        return null;
    }

    protected function getSurfaceProfile(int $searchDepth): array
    {
        $profile = [];
        $topY    = $this->highestPoint;

        // For each of the 7 columns, scan downward from the top
        for ($x = 0; $x < 7; $x++) {
            $maxY = -1;

            for ($y = $topY; $y >= max(0, $topY - $searchDepth); $y--) {
                if (isset($this->settledRocks[$y][$x]) && '#' === $this->settledRocks[$y][$x]) {
                    $maxY = $y;
                    break; // found the highest rock in this column, stop searching
                }
            }

            $profile[$x] = $maxY;
        }

        // Normalize to relative heights from the minimum
        $minY = min($profile);
        return array_map(fn ($y) => $y - $minY, $profile);
    }

    protected function canPlaceRock(array $rock, int $x, int $y): bool
    {
        $rockHeight = $rock['h'];

        foreach ($rock['r'] as $dy => $row) {
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

    protected function mergeRockIntoGrid(int $x, int $y, array $rock, int $pruneDepth): void
    {
        $rockHeight = $rock['h'];

        foreach ($rock['r'] as $dy => $row) {
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

        // prune settled rocks to keep only the top N rows for memory efficiency
        if (0 === $this->highestPoint % $pruneDepth) {
            $this->settledRocks = array_slice($this->settledRocks, -$pruneDepth, $pruneDepth, true);
        }
    }

    protected function renderGridWithRock(int $rockX, int $rockY, array $rock): void
    {
        $rockHeight = $rock['h'];

        // get terminal height (default to 40 if can't detect)
        $terminalHeight = (int) (shell_exec('tput lines') ?: 40);
        $terminalHeight = max(10, $terminalHeight - 3); // reserve space for info line and floor

        // determine visible Y range - show top of tower if it exceeds terminal height
        $minSettledY = !empty($this->settledRocks) ? min(array_keys($this->settledRocks)) : 0;
        $maxY        = max($this->highestPoint, $rockY + $rockHeight) + 2;
        $towerHeight = $maxY                                          + 1;

        // if tower exceeds terminal height, show only the top portion
        if ($towerHeight > $terminalHeight) {
            $minY = $maxY - $terminalHeight + 1;
        } else {
            $minY = 0;
        }

        // build sparse grid only for rows we need to display
        $grid = [];
        for ($y = $minY; $y <= $maxY; $y++) {
            $grid[$y] = array_fill(0, 7, '.');

            // place settled rocks if they exist at this y
            if (isset($this->settledRocks[$y])) {
                foreach ($this->settledRocks[$y] as $x => $cell) {
                    if ('#' === $cell) {
                        $grid[$y][$x] = '#';
                    }
                }
            }
        }

        // overlay current falling rock (invert rock coordinates to match upward y)
        foreach ($rock['r'] as $dy => $row) {
            foreach ($row as $dx => $cell) {
                if ('#' === $cell) {
                    $displayY = $rockY + ($rockHeight - 1 - $dy);
                    if (isset($grid[$displayY])) {
                        $grid[$displayY][$rockX + $dx] = '@';
                    }
                }
            }
        }

        // clear screen and move cursor to bottom
        echo "\e[2J";

        // render from top down (reverse order so floor is at bottom)
        $grid = array_reverse($grid, true);

        // move cursor to top of terminal
        echo "\e[H";

        $this->renderGrid($grid);
        printf(
            "Height: %d | Rock Y: %d | Visible: Y=%d-%d\n",
            $this->highestPoint,
            $rockY,
            $minY,
            $maxY
        );
    }

    protected function renderGrid(array $grid, int $width = 7): void
    {
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

    protected function parseInput(mixed $input): Collection
    {
        $input = is_array($input) ? $input : explode("\n", $input);

        // convert instructions into an array
        return collect(mb_str_split($input[0]));
    }
}
