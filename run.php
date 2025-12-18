<?php

/** Advent of Code 2022 PHP runner.
 *
 * Usage:
 * php run.php <options>
 * -d,--day PATTERN          Only run days that match pattern (range or comma-separated list)
 * -p,--part PATTERN         Only run parts that match pattern (range or comma-separated list)
 * -e,--examples             Runs the examples
 * -h,--help                 This help message
 *
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

use App\DayFactory;
use App\Runner\DTO\CliArg;
use App\Runner\DTO\CliArgType;
use App\Runner\ParseCliArgs;
use App\Runner\Runner;

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
