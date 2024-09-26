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
    //protected int $interactiveModePart2 = self::NO_INTERACTIVE;
    //protected int $interactiveModePart1 = self::INTERACTIVE_DELAY;
    protected int $interactiveModePart2 = self::INTERACTIVE_DELAY;

    protected int $delay = 50000;
    /**
     * Using your scan, simulate the falling sand. How many units of sand come to rest before sand starts flowing into the abyss below?
     */
    public function solvePart1(mixed $input): int|string|null
    {
        $input      = $this->parseInput($input);
        $maxY       = $input->flatten(1)->max('y');
        $sandSource = ['x' => 500, 'y' => 0];

        $grid = [];

        // fill the grid with the paths
        foreach ($input as $path) {
            for ($i = 1, $iMax = count($path); $i < $iMax; $i++) {
                $start  = $path[$i - 1];
                $end    = $path[$i];
                $xRange = range(min($start['x'], $end['x']), max($start['x'], $end['x']));
                $yRange = range(min($start['y'], $end['y']), max($start['y'], $end['y']));
                foreach ($yRange as $y) {
                    foreach ($xRange as $x) {
                        $key        = sprintf('%d,%d', $y, $x);
                        $grid[$key] = '#';
                    }
                }
            }
        }

        // add the sand source
        $grid[sprintf('%d,%d', $sandSource['y'], $sandSource['x'])] = '+';

        if ($this->interactiveModePart1) {
            printf("%s\n", $this->printGrid($grid));
        }

        $sandCount = 0;
        $frame     = 0;
        while (true) {
            $sand  = $sandSource;
            $moved = false;
            while (true) {
                $action = '';
                ++$frame;
                $prevSand = sprintf('%d,%d', $sand['y'], $sand['x']);

                if ($sand['y'] > $maxY) {
                    break 2;
                }

                $below     = $grid[sprintf('%d,%d', $sand['y'] + 1, $sand['x'])]     ?? '.';
                $diagLeft  = $grid[sprintf('%d,%d', $sand['y'] + 1, $sand['x'] - 1)] ?? '.';
                $diagRight = $grid[sprintf('%d,%d', $sand['y'] + 1, $sand['x'] + 1)] ?? '.';

                //dd($below, $diagLeft, $diagRight);

                if ('.' === $below) {
                    $action = "sand moved down";
                    $sand['y']++;
                    $moved = true;
                } elseif ('.' === $diagLeft) {
                    $action = "sand moved down and left";
                    $sand['y']++;
                    $sand['x']--;
                    $moved = true;
                } elseif ('.' === $diagRight) {
                    $action = "sand moved down and right";
                    $sand['y']++;
                    $sand['x']++;
                    $moved = true;
                } else {
                    $action = "sand came to rest";
                    ++$sandCount;
                    $grid[sprintf('%d,%d', $sand['y'], $sand['x'])] = 'o';
                    break;
                }

                if ($moved && $this->interactiveModePart1) {
                    $grid[$prevSand]                                = '.';
                    $grid[sprintf('%d,%d', $sand['y'], $sand['x'])] = '+';
                }

                // handle interactive mode
                if ($this->interactiveModePart1) {
                    printf(
                        "%s\n",
                        $this->printGrid(
                            $grid,
                            $sand,
                            sprintf(
                                "sand: %d, frame: %d, y,x: %d,%d, below: %s, diagLeft: %s, diagRight: %s action: %s\n",
                                $sandCount,
                                $frame,
                                $sand['y'],
                                $sand['x'],
                                $below,
                                $diagLeft,
                                $diagRight,
                                $action
                            )
                        )
                    );
                    if (self::INTERACTIVE_KB === $this->interactiveModePart1) {
                        echo "Press any key to continue...\n";
                        system('stty cbreak -echo');
                        $input = fread(STDIN, 1);
                        system('stty -cbreak echo');
                    } elseif (self::INTERACTIVE_DELAY === $this->interactiveModePart1) {
                        $this->delay && usleep($this->delay);
                    }
                }
            }
            if (!$moved) {
                break;
            }
        }

        if ($this->interactiveModePart1) {
            printf("%s\n", $this->printGrid($grid));
        }

        return $sandCount;
    }

    /**
     * Using your scan, simulate the falling sand until the source of the sand becomes blocked. How many units of sand come to rest?
     */
    public function solvePart2(mixed $input): int|string|null
    {
        $input      = $this->parseInput($input);
        $maxY       = $input->flatten(1)->max('y') + 2;
        $sandSource = ['x' => 500, 'y' => 0];

        $grid = [];

        // fill the grid with the paths
        foreach ($input as $path) {
            for ($i = 1, $iMax = count($path); $i < $iMax; $i++) {
                $start  = $path[$i - 1];
                $end    = $path[$i];
                $xRange = range(min($start['x'], $end['x']), max($start['x'], $end['x']));
                $yRange = range(min($start['y'], $end['y']), max($start['y'], $end['y']));
                foreach ($yRange as $y) {
                    foreach ($xRange as $x) {
                        $key        = sprintf('%d,%d', $y, $x);
                        $grid[$key] = '#';
                    }
                }
            }
        }

        // add the sand source
        $grid[sprintf('%d,%d', $sandSource['y'], $sandSource['x'])] = '+';

        $sandCount = 0;
        $frame     = 0;
        $directions = [
            [1, 0],  // down
            [1, -1], // down-left
            [1, 1]   // down-right
        ];

        while (true) {
            $sand = $sandSource;

            while (true) {
                $action = '';
                ++$frame;
                $prevSand = sprintf('%d,%d', $sand['y'], $sand['x']);

                // set the floor dynamically
                if ($sand['y'] + 1 >= $maxY) {
                    $grid[sprintf('%d,%d', $sand['y'] + 1, $sand['x'] - 1)] = '#';
                    $grid[sprintf('%d,%d', $sand['y'] + 1, $sand['x'])]     = '#';
                    $grid[sprintf('%d,%d', $sand['y'] + 1, $sand['x'] + 1)] = '#';
                }

                $moved = false;
                foreach ($directions as $direction) {
                    $newY = $sand['y'] + $direction[0];
                    $newX = $sand['x'] + $direction[1];
                    $key = sprintf('%d,%d', $newY, $newX);
                    if (!isset($grid[$key]) || $grid[$key] === '.') {
                        $sand['y'] = $newY;
                        $sand['x'] = $newX;
                        $moved = true;
                        $action = $direction[1] === 0 ? "sand moved down" : ($direction[1] === -1 ? "sand moved down and left" : "sand moved down and right");
                        break;
                    }
                }

                if (!$moved) {
                    ++$sandCount;
                    $action .= "sand came to rest";
                    $grid[sprintf('%d,%d', $sand['y'], $sand['x'])] = 'o';
                    // if the sand is at the source, break out of the loop
                    if (0 === $sand['y'] && 500 === $sand['x']) {
                        $action .= 'sand at source';
                        break 2;
                    }
                    break;
                }

                if ($moved && $this->interactiveModePart2) {
                    $grid[$prevSand]                                = '.';
                    $grid[sprintf('%d,%d', $sand['y'], $sand['x'])] = '+';
                }

                // handle interactive mode
                if ($this->interactiveModePart2) {
                    printf(
                        "%s\n",
                        $this->printGrid(
                            $grid,
                            $sand,
                            sprintf(
                                "sand: %d, frame: %d, y,x: %d,%d, action: %s\n",
                                $sandCount,
                                $frame,
                                $sand['y'],
                                $sand['x'],
                                $action,
                            )
                        )
                    );
                    if (self::INTERACTIVE_KB === $this->interactiveModePart2) {
                        echo "Press any key to continue...\n";
                        system('stty cbreak -echo');
                        $input = fread(STDIN, 1);
                        system('stty -cbreak echo');
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

        // Find grid dimensions
        $coordinates = array_map(fn ($key) => explode(',', $key), array_keys($grid));
        $yValues     = array_column($coordinates, 0);
        $xValues     = array_column($coordinates, 1);
        $minY        = min($yValues);
        $maxY        = max($yValues);
        $minX        = min($xValues);
        $maxX        = max($xValues);

        $gridHeight     = $maxY                          - $minY + 1;
        $gridWidth      = $maxX                          - $minX + 1;
        $terminalHeight = (int) shell_exec('tput lines') - 2; // Subtract 2 for message and prompt

        $startY = $minY;
        $endY   = $maxY + 1;

        if ($sand && $gridHeight > $terminalHeight) {
            $sandY        = $sand['y'];
            $halfTerminal = intdiv($terminalHeight, 2);

            $startY = max($minY, $sandY - $halfTerminal);
            $endY   = min($maxY + 1, $startY + $terminalHeight);

            // Adjust startY if endY is at the bottom of the grid
            if ($endY === $maxY + 1) {
                $startY = max($minY, $endY - $terminalHeight);
            }
        }

        $borderTopBottom = '+'.str_repeat('-', $gridWidth).'+';
        $borderedGrid    = [];

        for ($y = $startY; $y < $endY; $y++) {
            $row = '|';
            for ($x = $minX; $x <= $maxX; $x++) {
                $key  = "{$y},{$x}";
                $char = $grid[$key] ?? '.';
                $row .= $colorize($char);
            }
            $row .= '|';
            $borderedGrid[] = $row;
        }

        array_unshift($borderedGrid, $borderTopBottom);
        array_push($borderedGrid, $borderTopBottom);

        $output = implode("\n", $borderedGrid)."\n".$message;

        // Only update the screen if the output has changed
        if ($output !== $lastOutput) {
            if ($clear) {
                // Move cursor to top-left corner
                echo "\033[2J\033[;H";
            }
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
