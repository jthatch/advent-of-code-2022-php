<?php

declare(strict_types=1);

namespace App\Contracts;

interface DayInterface
{
    public const EXAMPLE1 = '';
    public const EXAMPLE2 = '';

    public function solvePart1(mixed $input): int|string|null;

    public function solvePart2(mixed $input): int|string|null;

    public function day(): string;
}
