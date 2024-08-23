<?php

declare(strict_types=1);

namespace App\Days;

use App\Contracts\Day;
use Illuminate\Support\Collection;

class Day2 extends Day
{
    public const EXAMPLE1 = <<<eof
        A Y
        B X
        C Z
        eof;

    /**
     * What would your total score be if everything goes exactly according to your strategy guide?
     */
    public function solvePart1(mixed $input): int|string|null
    {
        return $this->parseInput($input)
            ->map(fn ($chunk) => collect($chunk)->map(fn ($value) => match ($value) {
                'A', 'X' => 'rock',
                'B', 'Y' => 'paper',
                'C', 'Z' => 'scissors',
            })->toArray())
            ->map(function ($pair) {
                $ourShape = $pair[1];
                $scores   = [];
                // score for our shape
                $scores[] = $this->getShapeScore($ourShape);
                // score for outcome of the round
                $scores[] = match ($pair) {
                    // lost
                    ['rock', 'scissors'], ['scissors', 'paper'], ['paper', 'rock'] => 0,
                    // draw
                    ['rock', 'rock'], ['scissors', 'scissors'], ['paper', 'paper'] => 3,
                    // win
                    ['scissors', 'rock'], ['paper', 'scissors'], ['rock', 'paper'] => 6,
                };

                return array_sum($scores);
            })
            ->sum();
    }

    /**
     * what would your total score be if everything goes exactly according to your strategy guide?
     */
    public function solvePart2(mixed $input): int|string|null
    {
        return $this->parseInput($input)
            // Anyway, the second column says how the round needs to end:
            // X means you need to lose, Y means you need to end the round in a draw, and Z means you need to win.
            ->map(fn ($chunk) => collect($chunk)->map(fn ($value) => match ($value) {
                'A'     => 'rock',
                'B'     => 'paper',
                'C'     => 'scissors',
                'X'     => 'lose',
                'Y'     => 'draw',
                'Z'     => 'win',
                default => $value,
            })->toArray())
            ->map(function ($pair) {
                // calculate our shape based on how we need to play
                $ourShape = match ($pair) {
                    ['paper', 'lose'], ['rock', 'draw'], ['scissors', 'win'] => 'rock',
                    ['scissors', 'lose'], ['paper', 'draw'], ['rock', 'win'] => 'paper',
                    ['rock', 'lose'], ['scissors', 'draw'], ['paper', 'win'] => 'scissors',
                };
                $scores = [];
                // score for our shape
                $scores[] = $this->getShapeScore($ourShape);
                // score for outcome of the round
                $scores[] = match ($pair[1]) {
                    'lose'  => 0,
                    'draw'  => 3,
                    'win'   => 6,
                    default => 0,
                };

                return array_sum($scores);
            })
            ->sum();
    }

    protected function getShapeScore(string $shape): int
    {
        return match ($shape) {
            'rock'     => 1,
            'paper'    => 2,
            'scissors' => 3,
            default    => 0,
        };
    }

    protected function parseInput(mixed $input): Collection
    {
        $input = is_array($input) ? $input : explode("\n", $input);

        return collect($input)
            ->map(fn (string $line) => explode(' ', $line));
    }

    public function getExample2(): mixed
    {
        return static::EXAMPLE1;
    }
}
