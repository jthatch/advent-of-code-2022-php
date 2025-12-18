<?php

declare(strict_types=1);

namespace App\Runner;

readonly class Options
{
    /**
     * @param array<int>|null $days
     * @param array<int>|null $parts
     * @param bool $withExamples
     * @param bool $wantsHelp
     */
    public function __construct(
        public ?array $days,
        public ?array $parts,
        public bool $withExamples,
        public bool $wantsHelp = false
    ) {
    }
}
