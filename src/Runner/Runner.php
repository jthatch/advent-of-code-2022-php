<?php

declare(strict_types=1);

namespace App\Runner;

use App\Contracts\Day;
use App\DayFactory;
use Generator;
use Exception;
use Fiber;

class Runner implements RunnerInterface
{
    /**
     * @param Options $options
     * @param DayFactory $factory
     * @param array<int, int>|null $days
     */
    public function __construct(
        protected Options $options,
        protected DayFactory $factory = new DayFactory(),
        protected ?array $days = null
    ) {
        $this->days ??= $this->options->days;
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
        $this->options->withExamples && $this->runExamples($day);

        printf("\e[1;4m%s\e[0m\n", $day->day());
        foreach ([1, 2] as $part) {
            $this->shouldRunPart($part) && $this->runPart($day, $part);
        }
    }

    protected function runExamples(Day $day): void
    {
        printf("\e[1;4m%s Examples\e[0m\n", $day->day());
        foreach ([1, 2] as $part) {
            $this->shouldRunPart($part) && $this->runPartExamples($part, $day);
        }
    }

    protected function runPart(Day $day, int $part): void
    {
        $startTime = microtime(true);
        $method    = "solvePart{$part}";

        $isLongRunning  = false;
        $lastReportTime = $startTime;

        $day->setLongRunningCallback(function () use (&$isLongRunning, &$lastReportTime, $startTime, $part): void {
            $currentTime = microtime(true);
            if ($currentTime - $lastReportTime >= 1) {
                $isLongRunning  = true;
                $lastReportTime = $currentTime;
                printf("\r%s\r", str_repeat(" ", 80));
                printf("\r    Part{$part} \e[1;33mCalculating...\e[0m\n");
                $this->report($startTime, true);
                flush();
            }
        });
        $solveFiber = new Fiber(fn () => $day->$method($day->input));

        $solveFiber->start();

        while (!$solveFiber->isTerminated()) {
            $solveFiber->resume();
        }

        try {
            $result = $solveFiber->getReturn();

            if ($isLongRunning) {
                // Clear the line before printing the result
                printf("\r%s\r", str_repeat(" ", 80));
            }

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

        is_array($examples)
            ? $this->runMultipleExamples($part, $day, $examples, $solveMethod)
            : $this->runSingleExample($part, $day, $examples, $solveMethod);

        $this->report($startTime);
    }

    /**
     * @param int $part
     * @param Day $day
     * @param array<int, mixed> $examples
     * @param string $solveMethod
     */
    protected function runMultipleExamples(int $part, Day $day, array $examples, string $solveMethod): void
    {
        foreach ($examples as $i => $example) {
            $partLetter = chr(97 + $i);
            printf("    Part%d%s \e[1;32m%s\e[0m\n", $part, $partLetter, $day->$solveMethod($example));
        }
    }

    protected function runSingleExample(int $part, Day $day, mixed $example, string $solveMethod): void
    {
        printf("    Part%d Example \e[1;32m%s\e[0m\n", $part, $day->$solveMethod($example));
    }

    /**
     * @return Generator<Day>
     */
    protected function dayGenerator(): Generator
    {
        return null !== $this->days
            ? (function () {
                while (!empty($this->days)) {
                    yield $this->factory->create((int) array_shift($this->days));
                }
            })()
            : $this->factory->allAvailableDays();
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
            $this->colorise($this->humanReadableBytes($mem), $mem, 900000, 2000000),
            $this->colorise($this->humanReadableBytes($memPeak), $memPeak, 5e+7, 1e+8),
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
