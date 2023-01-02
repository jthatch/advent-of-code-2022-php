<?php

declare(strict_types=1);

namespace App\Runner;

use App\DayFactory;

class Runner
{
    protected ?array $days;

    public function __construct(protected Options $options, protected DayFactory $factory = new DayFactory())
    {
        $this->days = $this->options->days;
    }

    public function run(): void
    {
        if ($this->options->wantsHelp) {
            $this->showHelp();

            return;
        }

        $this->showStart();
    }

    protected function dayGenerator(): \Generator
    {
        // If days are passed on the command line, e.g. `php run.php 1` or `php run.php 1-5,6` our generator returns those days,
        // otherwise returns all days that have been solved.
        return null !== $this->days
            ? (function () {
                while (!empty($this->days)) {
                    yield $this->factory->create((int) array_pop($this->days));
                }
            })()
            : $this->factory->allAvailableDays();
    }

    protected function showStart(): void
    {
        printf(
            <<<eof
\e[32m---------------------------------------------
|\e[0m Advent of Code 2022 PHP - James Thatcher\e[32m  |
|\e[0m                                         \e[32m  |
|\e[0;37m Days: \e[2;37m%-34s \e[0;32m |
|\e[0;37m Part: \e[2;37m%-34s \e[0;32m |
|\e[0;37m With examples: \e[2;37m%-25s \e[0;32m |
---------------------------------------------\e[0m

eof,
            null === $this->options->days ? 'all' : implode(',', $this->options->days),
            null === $this->options->parts ? '1,2' : implode(',', $this->options->parts),
            $this->options->withExamples ? 'yes' : 'no'
        );
    }

    protected function showHelp(): void
    {
        printf(
            <<<eof
Advent of Code 2022 PHP runner.

Usage:
 php run.php <options>
    -d,--day=PATTERN          Only run days that match pattern (range or comma-separated list)
    -p,--part=PATTERN         Only run parts that match pattern (range or comma-separated list)
    -e,--examples             Runs the examples
    -h,--help                 This help message

eof
        );
    }
}
