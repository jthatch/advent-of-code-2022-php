<?php

declare(strict_types=1);

namespace App\Days;

use App\Contracts\Day;
use Illuminate\Support\Collection;

class Day8 extends Day
{
    public const EXAMPLE1 = <<<eof
        30373
        25512
        65332
        33549
        35390
        eof;

    /**
     *
     */
    public function solvePart1(mixed $input): int|string|null
    {
        $input = $this->parseInput($input);

        return '';
    }

    /**
     *
     */
    public function solvePart2(mixed $input): int|string|null
    {
        $input = $this->parseInput($input);

        return '';
    }

    protected function parseInput(mixed $input): Collection
    {
        $input = is_array($input) ? $input : explode("\n", $input);

        return collect($input);
    }
}
