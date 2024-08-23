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
        $input = $this->parseInput($input)
            ->map(fn ($chunk) => collect($chunk)->map(fn ($value) => match ($value) {
                'A', 'X' => 'rock',
                'B', 'Y' => 'paper',
                'C', 'Z' => 'scissors',
            })->toArray());

        return $input
            ->map(fn ($pair) => array_sum($this->solveRockPaperScissorsPair($pair)))
            ->sum();
    }

    /**
     * what would your total score be if everything goes exactly according to your strategy guide?
     */
    public function solvePart2(mixed $input): int|string|null
    {
        // todo
        return '';
    }

    public function solveRockPaperScissorsPair(array $pair): array
    {
        // score for i selected
        $scores[] = match ($pair[1]) {
            'rock'     => 1,
            'paper'    => 2,
            'scissors' => 3,
            default    => 0,
        };
        // score for outcome of the round
        $scores[] = match ($pair) {
            // i lost
            ['rock', 'scissors'], ['scissors', 'paper'], ['paper', 'rock'] => 0,
            // draw
            ['rock', 'rock'], ['scissors', 'scissors'], ['paper', 'paper'] => 3,
            // i win
            ['scissors', 'rock'], ['paper', 'scissors'], ['rock', 'paper'] => 6,
        };

        return $scores;
    }

    protected function parseInput(mixed $input): Collection
    {
        $input = is_array($input) ? $input : explode("\n", $input);

        return collect($input)
            ->map(fn (string $line) => explode(' ', $line));
    }
}
