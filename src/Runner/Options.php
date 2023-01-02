<?php

declare(strict_types=1);

namespace App\Runner;

readonly class Options
{
    public function __construct(public ?array $days, public ?array $parts, public bool $withExamples, public bool $wantsHelp = false)
    {
    }
}
