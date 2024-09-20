<?php

declare(strict_types=1);

namespace App\Days;

use App\Contracts\Day;
use Illuminate\Support\Collection;

class Day9 extends Day
{
    public const EXAMPLE1 = <<<eof
        R 4
        U 4
        L 3
        D 1
        R 4
        D 1
        L 5
        R 2
        eof;

    public const EXAMPLE2 = <<<eof
        R 5
        U 8
        L 8
        D 3
        R 17
        D 10
        L 25
        U 20
        eof;

    // directions we can travel
    private array $directions = [
        'U' => [-1, 0],
        'R' => [0, 1],
        'D' => [1, 0],
        'L' => [0, -1],
    ];

    /**
     * How many positions does the tail of the rope visit at least once?
     */
    public function solvePart1(mixed $input): int|string|null
    {
        $input = $this
            ->parseInput($input)
            ->toArray();

        $visited = [];
        $head    = [0, 0];
        $tail    = [0, 0];
        foreach ($input as [$direction, $amount]) {
            $pos = $this->directions[$direction];
            while ($amount-- > 0) {
                $head[0] += $pos[0];
                $head[1] += $pos[1];

                // now we gotta calculate where to move the tail based on the following rules:
                // If the head is ever two steps directly up, down, left, or right from the tail, the tail must also move one step in that direction so it remains close enough
                // Otherwise, if the head and tail aren't touching and aren't in the same row or column, the tail always moves one step diagonally to keep up
                $tail = $this->moveTail($head, $tail);

                $key           = sprintf('%s-%s', $tail[0], $tail[1]);
                $visited[$key] = 'T';
            }
        }

        return count($visited);
    }

    /**
     * How many positions does the tail of the rope visit at least once?
     */
    public function solvePart2(mixed $input): int|string|null
    {
        $input = $this
            ->parseInput($input)
            ->toArray();

        $visited = [];
        $knots   = array_fill(0, 10, [0, 0]);
        foreach ($input as [$direction, $amount]) {
            $pos = $this->directions[$direction];
            while ($amount-- > 0) {
                // move the head once in the direction
                $knots[0][0] += $pos[0];
                $knots[0][1] += $pos[1];

                foreach ($knots as $id => &$tail) {
                    // skip the head
                    if (0 === $id) {
                        continue;
                    }
                    $head = &$knots[$id - 1];
                    $tail = $this->moveTail($head, $tail);
                }
                unset($tail);
                $key           = sprintf('%s-%s', $knots[9][0], $knots[9][1]);
                $visited[$key] = $id;
            }
        }

        return count($visited);
    }

    protected function moveTail(array $head, array $tail): array
    {
        [$headX, $headY] = $head;
        [$tailX, $tailY] = $tail;
        if (2 === abs($headX - $tailX) + abs($headY - $tailY)) {
            // Head is two steps directly up, down, left, or right from the tail
            // Move the tail one step in the same direction as the head
            match (true) {
                2  === $headX - $tailX => ++$tail[0],
                -2 === $headX - $tailX => --$tail[0],
                2  === $headY - $tailY => ++$tail[1],
                -2 === $headY - $tailY => --$tail[1],
                default                => null
            };
        } elseif ($headX !== $tailX && $headY !== $tailY) {
            // Head and tail aren't touching and aren't in the same row or column
            // Move the tail one step diagonally to keep up
            $tail[0] += $headX <=> $tailX;
            $tail[1] += $headY <=> $tailY;
        }

        return $tail;
    }

    protected function parseInput(mixed $input): Collection
    {
        $input = is_array($input) ? $input : explode("\n", $input);

        return collect($input)
            ->map(fn ($line) => explode(' ', $line));
    }

    public function getExample2(): mixed
    {
        return static::EXAMPLE2;
    }
}
