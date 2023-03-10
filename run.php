<?php
/** Advent of Code 2022 PHP runner.
 *
 * Usage:
 * php run.php <options>
 * -d,--day PATTERN          Only run days that match pattern (range or comma-separated list)
 * -p,--part PATTERN         Only run parts that match pattern (range or comma-separated list)
 * -e,--examples             Runs the examples
 * -h,--help                 This help message

 *  php run.php --day=[day] --part=[part] --examples
 *  [day]  = optional - The day(s) to run. Can be a range (1-10) or comma-separated (1,2,5) including combination of both.
 *  [part] = optional - The part to run
 *  [withExamples] = optional - Will run the examples if defined in the Day class
 *
 * Examples:
 *  php run.php
 *      - Run all days
 *
 * php run.php --day=15 --examples
 *      - Run day 15 examples
 *
 *  php run.php --day=1-5,9
 *      - Run days 1-5 & 9
 *
 *  php run.php --day=10
 *      - Run day 10 part 1 & 2
 *
 *  php run.php --day=6,7 --part=2
 *      - Run days 6 & 7 part 2
 *
 *  php run.php --day=1-25 --examples
 *      - Run days 1-25 with examples
 */
declare(strict_types=1);

use App\Contracts\DayInterface;
use App\DayFactory;
use App\Runner\DTO\CliArg;
use App\Runner\DTO\CliArgType;
use App\Runner\ParseCliArgs;
use App\Runner\Runner;

$totalStartTime = microtime(true);
require 'vendor/autoload.php';

$cliArgs = [
    new CliArg(longName: 'day', type: CliArgType::WITH_VALUE),
    new CliArg(longName: 'part', type: CliArgType::WITH_VALUE),
    new CliArg('examples', type: CliArgType::NO_VALUE),
    new CliArg('help', type: CliArgType::NO_VALUE),
];

$cli = new ParseCliArgs(...$cliArgs);

$options = $cli->getOptions();
$runner  = new Runner($options, new DayFactory());

$runner->run();

// var_dump($cli->options);
exit;

// extract days from comma-seperated and ranged list (e.g. "1-3,5" would result in [1,2,3,5])
$onlyRunDays = $argv[1] ?? null
    ? array_reverse(array_merge([], ...array_map(fn (string $dayChunk) => (str_contains($dayChunk, '-') && [$start, $end] = sscanf($dayChunk, '%d-%d')) ? range($start, $end) : [$dayChunk], explode(',', $argv[1]))))
    : null;
$onlyRunPart = match ($argv[2] ?? null) {
    '1', '2' => (int) $argv[2],
    default => null,
};
$withExamples = isset($argv[3]);

// If days are passed on the command line, e.g. `php run.php 1` or `php run.php 1-5,6` our generator returns those days,
// otherwise returns all days that have been solved.
$dayGenerator = $onlyRunDays
    ? (static function () use (&$onlyRunDays) {
        while (!empty($onlyRunDays)) {
            yield DayFactory::create((int) array_pop($onlyRunDays));
        }
    })()
    : DayFactory::allAvailableDays();

printf(<<<eof
\e[32m---------------------------------------------
|\e[0m  Advent of Code 2022 PHP - James Thatcher\e[32m |
---------------------------------------------\e[0m

eof);

/** @var DayInterface $day */
foreach ($dayGenerator as $day) {
    printf("\e[1;4m%s\e[0m\n", $day->day());
    if (null === $onlyRunPart || 1 === $onlyRunPart) {
        $startTime = microtime(true);
        printf("    Part1 \e[1;32m%s\e[0m\n", $day->solvePart1());
        report($startTime);
    }
    if (null === $onlyRunPart || 2 === $onlyRunPart) {
        $startTime = microtime(true);
        printf("    Part2 \e[1;32m%s\e[0m\n", $day->solvePart2());
        report($startTime);
    }
}

printf(<<<eof
\e[32m---------------------------------------------
|\e[0m Total time: \e[2m%.5fs\e[0m                     \e[32m |
---------------------------------------------\e[0m

eof, microtime(true) - $totalStartTime);

function report(float $startTime): void
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
        $mem >= 1000000 => sprintf("\e[0;31m% 5s\e[0;2m", str_pad(humanReadableBytes($mem), 5)),
        $mem >= 750000  => sprintf("\e[1;31m% 5s\e[0;2m", str_pad(humanReadableBytes($mem), 5)),
        default         => sprintf('% 5s', str_pad(humanReadableBytes($mem), 5)),
    };

    $memPeakColourised = match (true) {
        $memPeak >= 1e+8 => sprintf("\e[0;31m% 7s\e[0;2m", str_pad(humanReadableBytes($memPeak), 5)),
        $memPeak >= 5e+7 => sprintf("\e[1;31m% 7s\e[0;2m", str_pad(humanReadableBytes($memPeak), 5)),
        default          => sprintf('% 7s', str_pad(humanReadableBytes($memPeak), 5)),
    };

    printf(
        "      \e[2mMem[%s] Peak[%s] Time[%s]\e[0m\n",
        $memColourised,
        $memPeakColourised,
        $timeColourised,
    );
}

function humanReadableBytes(int $bytes, int $precision = null): string
{
    $units          = ['b', 'kb', 'mb', 'gb', 'tb', 'pb', 'eb', 'zb', 'yb'];
    $precisionUnits = [0, 0, 1, 2, 2, 3, 3, 4, 4];

    return round(
        $bytes / (1024 ** ($i = floor(log($bytes, 1024)))),
        $precision ?? $precisionUnits[$i]
    ).$units[$i];
}
