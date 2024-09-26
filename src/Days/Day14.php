<?php

declare(strict_types=1);

namespace App\Days;

use App\Contracts\Day;
use Illuminate\Support\Collection;

class Day14 extends Day
{
    public const EXAMPLE1 = <<<eof
    498,4 -> 498,6 -> 496,6
    503,4 -> 502,4 -> 502,9 -> 494,9
    eof;

    protected const NO_INTERACTIVE    = 0;
    protected const INTERACTIVE_KB    = 1;
    protected const INTERACTIVE_DELAY = 2;

    protected int $interactiveModePart1 = self::NO_INTERACTIVE;
    //protected int $interactiveModePart1 = self::INTERACTIVE_DELAY;
    //protected int $interactiveModePart2 = self::INTERACTIVE_DELAY;
    protected int $interactiveModePart2 = self::NO_INTERACTIVE;

    protected int $delay = 10000; // 10000 = 10ms

    /**
     * Using your scan, simulate the falling sand. How many units of sand come to rest before sand starts flowing into the abyss below?
     */
    public function solvePart1(mixed $input): int|string|null
    {
        $input      = $this->parseInput($input);
        $maxY       = $input->flatten(1)->max('y');
        $sandSource = ['x' => 500, 'y' => 0];

        $grid = [];

        // pre-allocate the grid with a fixed size
        $minX = 500 - $maxY - 1;
        $maxX = 500 + $maxY + 1;
        for ($y = 0; $y <= $maxY; $y++) {
            for ($x = $minX; $x <= $maxX; $x++) {
                $grid[$y][$x] = '.';
            }
        }

        // fill the grid with the paths
        foreach ($input as $path) {
            for ($i = 1, $iMax = count($path); $i < $iMax; $i++) {
                $start  = $path[$i - 1];
                $end    = $path[$i];
                $xRange = range(min($start['x'], $end['x']), max($start['x'], $end['x']));
                $yRange = range(min($start['y'], $end['y']), max($start['y'], $end['y']));
                foreach ($yRange as $y) {
                    foreach ($xRange as $x) {
                        $grid[$y][$x] = '#';
                    }
                }
            }
        }

        // add the sand source
        $grid[$sandSource['y']][$sandSource['x']] = '+';

        if ($this->interactiveModePart1) {
            printf("%s\n", $this->printGrid($grid));
        }

        $sandCount = 0;
        $frame     = 0;

        while (true) {
            $sand = $sandSource;

            while (true) {
                $action = '';
                ++$frame;
                $prevY = $sand['y'];
                $prevX = $sand['x'];

                if ($sand['y'] >= $maxY) {
                    break 2;
                }

                // check directly below
                if (!isset($grid[$sand['y'] + 1][$sand['x']]) || '.' === $grid[$sand['y'] + 1][$sand['x']]) {
                    $sand['y']++;
                    $action = "sand moved down";
                }
                // check diagonally left
                elseif (!isset($grid[$sand['y'] + 1][$sand['x'] - 1]) || '.' === $grid[$sand['y'] + 1][$sand['x'] - 1]) {
                    $sand['y']++;
                    $sand['x']--;
                    $action = "sand moved down and left";
                }
                // check diagonally right
                elseif (!isset($grid[$sand['y'] + 1][$sand['x'] + 1]) || '.' === $grid[$sand['y'] + 1][$sand['x'] + 1]) {
                    $sand['y']++;
                    $sand['x']++;
                    $action = "sand moved down and right";
                } else {
                    ++$sandCount;
                    $action                       = "sand came to rest";
                    $grid[$sand['y']][$sand['x']] = 'o';
                    break;
                }

                // handle interactive mode
                if ($this->interactiveModePart1) {
                    $grid[$prevY][$prevX]         = '.';
                    $grid[$sand['y']][$sand['x']] = '+';
                    printf("%s\n", $this->printGrid($grid, $sand, sprintf(
                        "sand: %d, frame: %d, y,x: %d,%d, action: %s\n",
                        $sandCount,
                        $frame,
                        $sand['y'],
                        $sand['x'],
                        $action
                    )));
                    if (self::INTERACTIVE_KB === $this->interactiveModePart1) {
                        $this->waitForKeyPress();
                    } elseif (self::INTERACTIVE_DELAY === $this->interactiveModePart1) {
                        $this->delay && usleep($this->delay);
                    }
                }
            }
        }

        if ($this->interactiveModePart1) {
            printf("%s\n", $this->printGrid($grid));
        }

        return $sandCount;
    }

    /**
     * Using your scan, simulate the falling sand until the source of the sand becomes blocked. How many units of sand come to rest?
     * 1.0s to 0.1s performance improvements:
     * - use 2d array instead of hashmap
     * - pre-allocate the grid with a fixed size
     * - use isset() to check if a cell is empty instead of using the null coalescing operator
     */
    public function solvePart2(mixed $input): int|string|null
    {
        $input      = $this->parseInput($input);
        $maxY       = $input->flatten(1)->max('y') + 2;
        $sandSource = ['x' => 500, 'y' => 0];

        $grid = [];

        // Pre-allocate the grid with a fixed size
        $minX = 500 - $maxY;
        $maxX = 500 + $maxY;
        for ($y = 0; $y <= $maxY; $y++) {
            for ($x = $minX; $x <= $maxX; $x++) {
                $grid[$y][$x] = '.';
            }
        }

        // Fill the grid with the paths
        foreach ($input as $path) {
            for ($i = 1, $iMax = count($path); $i < $iMax; $i++) {
                $start  = $path[$i - 1];
                $end    = $path[$i];
                $xRange = range(min($start['x'], $end['x']), max($start['x'], $end['x']));
                $yRange = range(min($start['y'], $end['y']), max($start['y'], $end['y']));
                foreach ($yRange as $y) {
                    foreach ($xRange as $x) {
                        $grid[$y][$x] = '#';
                    }
                }
            }
        }

        // Add the sand source
        $grid[$sandSource['y']][$sandSource['x']] = '+';

        // Add the floor
        for ($x = $minX; $x <= $maxX; $x++) {
            $grid[$maxY][$x] = '#';
        }

        $sandCount = 0;
        $frame     = 0;

        while (true) {
            $sand = $sandSource;

            while (true) {
                $action = '';
                ++$frame;
                $prevY = $sand['y'];
                $prevX = $sand['x'];

                // check directly below
                if ('.' === $grid[$sand['y'] + 1][$sand['x']]) {
                    $sand['y']++;
                    $action = "sand moved down";
                }
                // check diagonally left
                elseif ('.' === $grid[$sand['y'] + 1][$sand['x'] - 1]) {
                    $sand['y']++;
                    $sand['x']--;
                    $action = "sand moved down and left";
                }
                // check diagonally right
                elseif ('.' === $grid[$sand['y'] + 1][$sand['x'] + 1]) {
                    $sand['y']++;
                    $sand['x']++;
                    $action = "sand moved down and right";
                } else {
                    ++$sandCount;
                    $action                       = "sand came to rest";
                    $grid[$sand['y']][$sand['x']] = 'o';
                    // if the sand is at the source, break out of the loop
                    if (0 === $sand['y'] && 500 === $sand['x']) {
                        $action .= 'sand at source';
                        break 2;
                    }
                    break;
                }

                // handle interactive mode
                if ($this->interactiveModePart2) {
                    $grid[$prevY][$prevX]         = '.';
                    $grid[$sand['y']][$sand['x']] = '+';
                    printf("%s\n", $this->printGrid($grid, $sand, sprintf(
                        "sand: %d, frame: %d, y,x: %d,%d, action: %s\n",
                        $sandCount,
                        $frame,
                        $sand['y'],
                        $sand['x'],
                        $action,
                    )));
                    if (self::INTERACTIVE_KB === $this->interactiveModePart2) {
                        $this->waitForKeyPress();
                    } elseif (self::INTERACTIVE_DELAY === $this->interactiveModePart2) {
                        $this->delay && usleep($this->delay);
                    }
                }
            }
        }

        if ($this->interactiveModePart2) {
            printf("%s\n", $this->printGrid($grid));
        }

        return $sandCount;
    }

    /**
     * Print the grid to the terminal. If the height of the grid exceeds the terminal height, the grid is scrolled.
     *
     * @param array $grid
     * @param array|null $sand
     * @param string $message
     * @param boolean $clear
     * @return string
     */
    protected function printGrid(array $grid, ?array $sand = null, string $message = '', bool $clear = true)
    {
        static $lastOutput = '';

        $colorize = function (string $char): string {
            return match ($char) {
                '#'     => "\033[1;33m#\033[0m", // yellow for rocks
                '.'     => "\033[1;37m.\033[0m", // white for empty space
                '+'     => "\033[1;34m+\033[0m", // blue for falling sand
                'o'     => "\033[1;31mo\033[0m", // red for resting sand
                '|'     => "\033[1;90m|\033[0m", // dark grey for border
                '-'     => "\033[1;90m-\033[0m", // dark grey for border
                '+'     => "\033[1;90m+\033[0m", // dark grey for border
                default => $char,
            };
        };

        $gridHeight     = count($grid);
        $gridWidth      = count($grid[0]);
        $terminalHeight = (int) shell_exec('tput lines') - 2; // Subtract 2 for message and prompt

        $startY = 0;
        $endY   = $gridHeight;

        if ($sand && $gridHeight > $terminalHeight) {
            $sandY        = $sand['y'];
            $halfTerminal = intdiv($terminalHeight, 2);

            $startY = max(0, $sandY - $halfTerminal);
            $endY   = min($gridHeight, $startY + $terminalHeight);

            // Adjust startY if endY is at the bottom of the grid
            if ($endY === $gridHeight) {
                $startY = max(0, $endY - $terminalHeight);
            }
        }

        $borderTopBottom = '+'.str_repeat('-', $gridWidth).'+';
        $borderedGrid    = array_slice($grid, $startY, $endY - $startY);
        $borderedGrid    = array_map(fn (array $row) => '|'.implode('', array_map($colorize, $row)).'|', $borderedGrid);
        array_unshift($borderedGrid, $borderTopBottom);
        array_push($borderedGrid, $borderTopBottom);

        $output = implode("\n", $borderedGrid)."\n".$message;

        // Only update the screen if the output has changed
        if ($output !== $lastOutput) {
            if ($clear) {
                // Move cursor to top-left corner
                echo "\033[2J\033[;H";

            }
            // Use ANSI escape codes to clear the screen from cursor to end
            //echo "\033[J" . $output;
            $lastOutput = $output;
        }

        return $output;
    }

    protected function waitForKeyPress(): void
    {
        echo "Press any key to continue...\n";
        system('stty cbreak -echo');
        $input = fread(STDIN, 1);
        system('stty -cbreak echo');
    }

    /**
     * Parse the input data.
     */
    protected function parseInput(mixed $input): Collection
    {
        $input = is_array($input) ? $input : explode("\n", $input);

        return collect($input)
            ->map(fn (string $line) => explode(' -> ', $line))
            ->map(
                fn (array $points) => collect($points)
                    ->map(fn (string $point): array => array_map('intval', explode(',', $point)))
                    ->map(fn (array $point): array => ['x' => $point[0], 'y' => $point[1]])
                    ->toArray()
            )
        ;
    }
}
