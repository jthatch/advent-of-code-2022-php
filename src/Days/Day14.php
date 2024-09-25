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

    protected int $interactiveModePart1 = self::INTERACTIVE_DELAY;
    //protected int $interactiveModePart1 = self::NO_INTERACTIVE;
    protected int $interactiveModePart2 = self::INTERACTIVE_DELAY;
    /**
     * Using your scan, simulate the falling sand. How many units of sand come to rest before sand starts flowing into the abyss below?
     */
    public function solvePart1(mixed $input): int|string|null
    {
        $input = $this->parseInput($input);
        $minX  = $input->flatten(1)->min('x');
        $maxX  = $input->flatten(1)->max('x');
        $minY  = $input->flatten(1)->min('y');
        $maxY  = $input->flatten(1)->max('y');
        // create the grid
        $grid = array_fill(0, $maxY + 1, array_fill($minX - 1, ($maxX - $minX) + 2, '.'));

        // fill the grid with the paths
        foreach ($input as $path) {
            for ($i = 1, $iMax = count($path); $i < $iMax; $i++) {
                [$x1, $y1] = [$path[$i - 1]['x'], $path[$i - 1]['y']];
                [$x2, $y2] = [$path[$i]['x'], $path[$i]['y']];

                if ($x1 === $x2) {
                    // vertical line
                    foreach (range(min($y1, $y2), max($y1, $y2)) as $y) {
                        $grid[$y][$x1] = '#';
                    }
                } elseif ($y1 === $y2) {
                    // horizontal line
                    foreach (range(min($x1, $x2), max($x1, $x2)) as $x) {
                        $grid[$y1][$x] = '#';
                    }
                }
            }
        }
        // position of the sand source
        $sand = ['x' => 500, 'y' => 0];
        // add the source of the sand
        $grid[$sand['y']][$sand['x']] = '+';
        if ($this->interactiveModePart1) {
            printf("minX: %d, maxX: %d, minY: %d, maxY: %d\n", $minX, $maxX, $minY, $maxY);
            // print the grid before the sand starts falling
            printf("%s\n", $this->printGrid($grid));
        }

        $sandCount = 0;
        $frame     = 0;
        while (true) {
            // reset sand
            $sand = ['x' => 500, 'y' => 0];
            // track if the sand has moved
            $moved = false;
            while (true) {
                $action = '';
                ++$frame;
                // draw the sand on the grid
                if ($this->interactiveModePart1) {
                    $grid[$sand['y']][$sand['x']] = '+';
                }
                if ($sand['y'] >= $maxY) {
                    break 2;
                }
                $below         = $grid[$sand['y'] + 1][$sand['x']]     ?? null;
                $diagonalLeft  = $grid[$sand['y'] + 1][$sand['x'] - 1] ?? null;
                $diagonalRight = $grid[$sand['y'] + 1][$sand['x'] + 1] ?? null;

                if (in_array($below, ['.', '+'])) {
                    $action = "sand moved down\n";
                    $sand['y']++;
                    $moved = true;
                } elseif (in_array($diagonalLeft, ['.', '+'])) {
                    $action = "sand moved down and left\n";
                    $sand['y']++;
                    $sand['x']--;
                    $moved = true;
                } elseif (in_array($diagonalRight, ['.', '+'])) {
                    $action = "sand moved down and right\n";
                    $sand['y']++;
                    $sand['x']++;
                } else {
                    ++$sandCount;
                    $action = "sand came to rest\n";
                    // clear the sand source
                    if ($this->interactiveModePart1) {
                        $grid[$sand['y']][$sand['x']] = '.';
                    }
                    $grid[$sand['y']][$sand['x']] = 'o';
                    break;
                }
                // handle interactive mode
                if ($this->interactiveModePart1) {
                    printf(
                        "%s\n",
                        $this->printGrid(
                            $grid,
                            $sand,
                            sprintf("sand: %d, frame: %d, y,x: %d,%d, below: %s, diagonalLeft: %s, diagonalRight: %s action: %s\n", $sandCount, $frame, $sand['y'], $sand['x'], $below, $diagonalLeft, $diagonalRight, $action)
                        )
                    );
                    if (self::INTERACTIVE_KB === $this->interactiveModePart1) {
                        echo "Press any key to continue...\n";
                        system('stty cbreak -echo');
                        $input = fread(STDIN, 1);
                        system('stty -cbreak echo');
                    } elseif (self::INTERACTIVE_DELAY === $this->interactiveModePart1) {
                        usleep(50000);
                    }
                }

            }
            if (!$moved) {
                break; // Break out of the outer loop if the sand did not move
            }
        }

        return $sandCount;
    }

    /**
     * Solve Part 2 of the day's problem.
     */
    public function solvePart2(mixed $input): int|string|null
    {
        $input = $this->parseInput($input);

        // todo: implement solution for Part 2

        return null;
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
