<?php

declare(strict_types=1);

namespace App\Contracts;

interface DayInterface
{
    public function solvePart1(mixed $input): int|string|null;

    public function solvePart2(mixed $input): int|string|null;

    public function day(): string;
}
