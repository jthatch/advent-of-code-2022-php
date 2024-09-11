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

    // directions we can travel
    private array $directions = [
        'U' => [-1, 0],
        'R' => [0, 1],
        'D' => [1, 0],
        'L' => [0, -1]
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
        $head = [0,0];
        $tail = [0,0];
        foreach($input as [$direction, $amount]) {
            $pos = $this->directions[$direction];
            //printf("direction: %s amount: %s pos: %s\n", $direction, $amount, implode(',', $pos));
            foreach(range(1, $amount) as $i) {
                $head[0] += $pos[0];
                $head[1] += $pos[1];
                // now we gotta calculate where to move the tail based on the following rules:
                // If the head is ever two steps directly up, down, left, or right from the tail, the tail must also move one step in that direction so it remains close enough
                // Otherwise, if the head and tail aren't touching and aren't in the same row or column, the tail always moves one step diagonally to keep up

                [$headX, $headY] = $head;
                [$tailX, $tailY] = $tail;
                if (abs($headX - $tailX) + abs($headY - $tailY) === 2) {
                    // Head is two steps directly up, down, left, or right from the tail
                    // Move the tail one step in the same direction as the head
                    match (true) {
                        $headX - $tailX === 2 => ++$tail[0],
                        $headX - $tailX === -2 => --$tail[0],
                        $headY - $tailY === 2 => ++$tail[1],
                        $headY - $tailY === -2 => --$tail[1],
                        default => null
                    };
                } elseif ($headX !== $tailX && $headY !== $tailY) {
                    // Head and tail aren't touching and aren't in the same row or column
                    // Move the tail one step diagonally to keep up
                    $tail[0] += $headX <=> $tailX;
                    $tail[1] += $headY <=> $tailY;
                }

                $key = sprintf('%s-%s', $tail[0], $tail[1]);
                $visited[$key] = 'T';

            }
        }

        return count($visited);
    }

    public function solvePart2(mixed $input): int|string|null
    {
        $input = $this
            ->parseInput($input)
            ->toArray();

        return '';
    }

    protected function parseInput(mixed $input): Collection
    {
        $input = is_array($input) ? $input : explode("\n", $input);

        return collect($input)
            ->map(fn ($line) => explode(' ', $line));
    }
}
