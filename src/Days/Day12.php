<?php

declare(strict_types=1);

namespace App\Days;

use App\Contracts\Day;
use Illuminate\Support\Collection;
use SplQueue;

class Day12 extends Day
{
    public const EXAMPLE1 = <<<eof
    Sabqponm
    abcryxxl
    accszExk
    acctuvwj
    abdefghi
    eof;

    private array $directions = [[-1, 0], [1, 0], [0, -1], [0, 1]]; // up, down, left, right

    /**
     * Solve Part 1 of the day's problem.
     */
    public function solvePart1(mixed $input): int|string|null
    {
        $grid = $this->parseInput($input)->toArray();

        $rows = count($grid);
        $cols = count($grid[0]);

        // Find start and end positions
        $start = null;
        $end   = null;
        foreach ($grid as $y => $row) {
            foreach ($row as $x => $cell) {
                match ($cell) {
                    'S'     => [$start, $grid[$y][$x]] = [[$y, $x], 'a'],
                    'E'     => [$end, $grid[$y][$x]]   = [[$y, $x], 'z'],
                    default => null,
                };
            }
        }

        // Breadth-First Search
        $queue = new SplQueue();
        $queue->enqueue([$start, 0]); // [position, steps]
        $visited                       = array_fill(0, $rows, array_fill(0, $cols, false));
        $visited[$start[0]][$start[1]] = true;

        while (!$queue->isEmpty()) {
            [$current, $steps] = $queue->dequeue();
            [$x, $y]           = $current;

            if ($current === $end) {
                return $steps; // Found the shortest path
            }

            foreach ($this->directions as [$dx, $dy]) {
                $newX = $x + $dx;
                $newY = $y + $dy;

                // check if the new position is within the grid bounds
                // check if the new position has not been visited
                // check if the new position is at most one level higher than the current position
                if ($newX >= 0 && $newX < $rows && $newY >= 0 && $newY < $cols
                               && !$visited[$newX][$newY]
                               && ord($grid[$newX][$newY]) <= ord($grid[$x][$y]) + 1
                ) {
                    $queue->enqueue([[$newX, $newY], $steps + 1]);
                    $visited[$newX][$newY] = true;
                }
            }
        }

        return null; // No path found
    }

    /**
     * Solve Part 2 of the day's problem.
     */
    public function solvePart2(mixed $input): int|string|null
    {
        $grid = $this->parseInput($input)->toArray();

        $rows = count($grid);
        $cols = count($grid[0]);

        // Find start and end positions
        $startingPoints = [];
        $end            = null;
        foreach ($grid as $y => $row) {
            foreach ($row as $x => $cell) {
                match ($cell) {
                    'S'     => [$startingPoints[], $grid[$y][$x]] = [[$y, $x], 'a'],
                    'E'     => [$end, $grid[$y][$x]]              = [[$y, $x], 'z'],
                    'a'     => $startingPoints[]                  = [$y, $x],
                    default => null,
                };
            }
        }

        return $this->bfs($grid, $startingPoints, $end, $rows, $cols);
    }

    protected function bfs(array $grid, array $startingPoints, array $end, int $rows, int $cols): int|string|null
    {
        $queue = new SplQueue();
        foreach ($startingPoints as $point) {
            $queue->enqueue([$point, 0]); // [position, steps]
        }

        $visited = array_fill(0, $rows, array_fill(0, $cols, false));
        foreach ($startingPoints as $point) {
            $visited[$point[0]][$point[1]] = true;
        }

        while (!$queue->isEmpty()) {
            [$current, $steps] = $queue->dequeue();
            [$x, $y]           = $current;

            if ($current === $end) {
                return $steps; // found the shortest path
            }

            foreach ($this->directions as [$dx, $dy]) {
                $newX = $x + $dx;
                $newY = $y + $dy;

                if ($newX >= 0 && $newX < $rows && $newY >= 0 && $newY < $cols
                               && !$visited[$newX][$newY]
                               && ord($grid[$newX][$newY]) <= ord($grid[$x][$y]) + 1
                ) {
                    $queue->enqueue([[$newX, $newY], $steps + 1]);
                    $visited[$newX][$newY] = true;
                }
            }
        }

        return null; // no path found
    }

    /**
     * Parse the input data.
     */
    protected function parseInput(mixed $input): Collection
    {
        $input = is_array($input) ? $input : explode("\n", $input);

        return collect($input)
            // convert each line into a 2D array
            ->map(fn (string $line): array => mb_str_split($line))

        ;
    }
}
