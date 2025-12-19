<?php

declare(strict_types=1);

namespace App\Runner;

use App\Contracts\Day;
use App\DayFactory;
use Generator;
use Exception;

readonly class Runner implements RunnerInterface
{
    /** @var array<int, int>|null */
    private ?array $days;

    public function __construct(
        private Options $options,
        private DayFactory $factory = new DayFactory(),
        ?array $days = null
    ) {
        $this->days = $days ?? $this->options->days;
    }

    public function run(): void
    {
        match (true) {
            $this->options->wantsHelp => $this->showHelp(),
            default                   => $this->runDays(),
        };
    }

    protected function runDays(): void
    {
        $this->showStart();
        $totalStartTime = microtime(true);

        foreach ($this->dayGenerator() as $day) {
            $this->runDay($day);
        }

        $this->showTotalTime($totalStartTime);
    }

    protected function runDay(Day $day): void
    {
        if ($this->options->withExamples) {
            $this->runExamples($day);
        }

        printf("\e[1;4m%s\e[0m\n", $day->day());
        foreach ([1, 2] as $part) {
            if ($this->shouldRunPart($part)) {
                $this->runPart($day, $part);
            }
        }
    }

    protected function runExamples(Day $day): void
    {
        printf("\e[1;4m%s Examples\e[0m\n", $day->day());
        foreach ([1, 2] as $part) {
            if ($this->shouldRunPart($part)) {
                $this->runPartExamples($part, $day);
            }
        }
    }

    protected function runPart(Day $day, int $part): void
    {
        $startTime = microtime(true);
        $method    = "solvePart{$part}";

        try {
            $result = $day->$method($day->input);
            printf("    Part{$part} \e[1;32m%s\e[0m\n", $result);
        } catch (Exception $e) {
            printf("    Part{$part} \e[1;31mError: %s\e[0m\n", $e->getMessage());
        }

        $this->report($startTime);
    }

    protected function runPartExamples(int $part, Day $day): void
    {
        $startTime     = microtime(true);
        $exampleMethod = "getExample{$part}";
        $solveMethod   = "solvePart{$part}";
        $examples      = $day->$exampleMethod();

        if (is_array($examples)) {
            $this->runMultipleExamples($part, $day, $examples, $solveMethod);
        } else {
            $this->runSingleExample($part, $day, $examples, $solveMethod);
        }

        $this->report($startTime);
    }

    /** @param array<int, mixed> $examples */
    protected function runMultipleExamples(int $part, Day $day, array $examples, string $solveMethod): void
    {
        foreach ($examples as $i => $example) {
            $partLetter = chr(65 + $i);
            printf("    Part%d%s \e[1;32m%s\e[0m\n", $part, $partLetter, $day->$solveMethod($example));
        }
    }

    protected function runSingleExample(int $part, Day $day, mixed $example, string $solveMethod): void
    {
        printf("    Part%d Example \e[1;32m%s\e[0m\n", $part, $day->$solveMethod($example));
    }

    /** @return Generator<Day> */
    protected function dayGenerator(): Generator
    {
        if (null !== $this->days) {
            foreach ($this->days as $day) {
                yield $this->factory->create($day);
            }
        } else {
            yield from $this->factory->allAvailableDays();
        }
    }

    protected function showStart(): void
    {
        printf(
            <<<EOF
            \e[32m---------------------------------------------
            |\e[0m Advent of Code 2022 PHP - James Thatcher\e[32m  |
            |\e[0m                                         \e[32m  |
            |\e[0;37m Days: \e[2;37m%-34s \e[0;32m |
            |\e[0;37m Part: \e[2;37m%-34s \e[0;32m |
            |\e[0;37m With Examples: \e[2;37m%-25s \e[0;32m |
            ---------------------------------------------\e[0m

            EOF,
            null === $this->options->days ? 'all' : implode(',', $this->options->days),
            null === $this->options->parts ? '1,2' : implode(',', $this->options->parts),
            $this->options->withExamples ? 'yes' : 'no'
        );
    }

    protected function showHelp(): void
    {
        echo <<<EOF
            Advent of Code 2022 PHP runner.

            Usage:
             php run.php <options>
                -d,--day=PATTERN          Only run days that match pattern (range or comma-separated list)
                -p,--part=PATTERN         Only run parts that match pattern (range or comma-separated list)
                -e,--examples             Runs the examples
                -h,--help                 This help message

            EOF;
    }

    protected function report(float $startTime): void
    {
        $time    = microtime(true) - $startTime;
        $mem     = memory_get_usage();
        $memPeak = memory_get_peak_usage();

        printf(
            "      \e[2mMem[%s] Peak[%s] Time[%s]\e[0m\n",
            $this->colorise($this->humanReadableBytes($mem), $mem, 900_000, 2_000_000),
            $this->colorise($this->humanReadableBytes($memPeak), $memPeak, 50_000_000, 100_000_000),
            $this->colorise($this->formatTime($time), $time, 0.1, 0.75),
        );
    }

    protected function formatTime(float $time): string
    {
        return match (true) {
            $time < 10   => sprintf('%.5fs', $time),
            $time < 100  => sprintf('%.4fs', $time),
            $time < 1000 => sprintf('%.3fs', $time),
            default      => sprintf('%.2fs', $time),
        };
    }

    protected function colorise(string $value, float|int $metric, float|int $warnThreshold, float|int $errorThreshold): string
    {
        return match (true) {
            $metric >= $errorThreshold => sprintf("\e[0;31m%s\e[0;2m", $value),
            $metric >= $warnThreshold  => sprintf("\e[1;31m%s\e[0;2m", $value),
            default                    => $value,
        };
    }

    protected function humanReadableBytes(int $bytes): string
    {
        $units = ['b', 'kb', 'mb', 'gb', 'tb', 'pb', 'eb', 'zb', 'yb'];
        $i     = floor(log($bytes, 1024));

        return sprintf(
            '%.*f%s',
            [0, 0, 1, 2, 2, 3, 3, 4, 4][$i],
            $bytes / (1024 ** $i),
            $units[$i]
        );
    }

    protected function shouldRunPart(int $part): bool
    {
        return null === $this->options->parts || in_array($part, $this->options->parts, true);
    }

    protected function showTotalTime(float $totalStartTime): void
    {
        printf(<<<EOF
        \e[32m---------------------------------------------
        |\e[0m Total time: \e[2m%.5fs\e[0m                     \e[32m |
        ---------------------------------------------\e[0m
        
        EOF, microtime(true) - $totalStartTime);
    }
}
