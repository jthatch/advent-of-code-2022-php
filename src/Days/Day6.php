<?php

declare(strict_types=1);

namespace App\Days;

use App\Contracts\Day;
use Illuminate\Support\Collection;

class Day6 extends Day
{
    public const EXAMPLE1 = <<<eof
        mjqjpqmgbljsphdztnvjfqwrcgsmlb
        eof;

    public function solvePart1(mixed $input): int|string|null
    {
        $input = $this->parseInput($input);

        return '';
    }

    public function solvePart2(mixed $input): int|string|null
    {
        return '';
    }

    protected function parseInput(mixed $input): Collection
    {
        $input = is_array($input) ? $input : explode("\n", $input);

        return collect($input);
    }
}
