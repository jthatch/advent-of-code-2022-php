<?php

declare(strict_types=1);

namespace App\Runner;

use App\Contracts\Day;
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
        $totalStartTime = microtime(true);

        /** @var Day $day */
        foreach ($this->dayGenerator() as $day) {
            // run examples first
            if ($this->options->withExamples) {
                printf("\e[1;4m%s Examples\e[0m\n", $day->day());
                if (null === $this->options->parts || in_array(1, $this->options->parts ?? [], true)) {
                    $startTime = microtime(true);
                    printf("    Part1 Example \e[1;32m%s\e[0m\n", $day->solvePart1($day->getExample1()));
                    $this->report($startTime);
                }

                if (null === $this->options->parts || in_array(2, $this->options->parts ?? [], true)) {
                    $startTime = microtime(true);
                    printf("    Part2 Example \e[1;32m%s\e[0m\n", $day->solvePart2($day->getExample2()));
                    $this->report($startTime);
                }
            }

            printf("\e[1;4m%s\e[0m\n", $day->day());
            if (null === $this->options->parts || in_array(1, $this->options->parts ?? [], true)) {
                $startTime = microtime(true);
                printf("    Part1 \e[1;32m%s\e[0m\n", $day->solvePart1($day->input));
                $this->report($startTime);
            }

            if (null === $this->options->parts || in_array(2, $this->options->parts ?? [], true)) {
                $startTime = microtime(true);
                printf("    Part2 \e[1;32m%s\e[0m\n", $day->solvePart2($day->input));
                $this->report($startTime);
            }
        }

        printf(<<<eof
        \e[32m---------------------------------------------
        |\e[0m Total time: \e[2m%.5fs\e[0m                     \e[32m |
        ---------------------------------------------\e[0m
        
        eof, microtime(true) - $totalStartTime);
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
            |\e[0;37m With Examples: \e[2;37m%-25s \e[0;32m |
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

    protected function report(float $startTime): void
    {
        $time           = microtime(true) - $startTime;
        $mem            = memory_get_usage();
        $memPeak        = memory_get_peak_usage();
        $timeColourised = match (true) {
            $time >= 0.75 => sprintf("\e[0;31m%.5fs\e[0;2m", $time),
            $time >= 0.1  => sprintf("\e[1;31m%.5fs\e[0;2m", $time),
            default       => sprintf('%.5fs', $time),
        };
        $memColourised = match (true) {
            $mem >= 1000000 => sprintf("\e[0;31m% 5s\e[0;2m", str_pad($this->humanReadableBytes($mem), 5)),
            $mem >= 750000  => sprintf("\e[1;31m% 5s\e[0;2m", str_pad($this->humanReadableBytes($mem), 5)),
            default         => sprintf('% 5s', str_pad($this->humanReadableBytes($mem), 5)),
        };

        $memPeakColourised = match (true) {
            $memPeak >= 1e+8 => sprintf("\e[0;31m% 7s\e[0;2m", str_pad($this->humanReadableBytes($memPeak), 5)),
            $memPeak >= 5e+7 => sprintf("\e[1;31m% 7s\e[0;2m", str_pad($this->humanReadableBytes($memPeak), 5)),
            default          => sprintf('% 7s', str_pad($this->humanReadableBytes($memPeak), 5)),
        };

        printf(
            "      \e[2mMem[%s] Peak[%s] Time[%s]\e[0m\n",
            $memColourised,
            $memPeakColourised,
            $timeColourised,
        );
    }

    protected function humanReadableBytes(int $bytes, int $precision = null): string
    {
        $units          = ['b', 'kb', 'mb', 'gb', 'tb', 'pb', 'eb', 'zb', 'yb'];
        $precisionUnits = [0, 0, 1, 2, 2, 3, 3, 4, 4];

        return round(
            $bytes / (1024 ** ($i = floor(log($bytes, 1024)))),
            $precision ?? $precisionUnits[$i]
        ).$units[$i];
    }
}
