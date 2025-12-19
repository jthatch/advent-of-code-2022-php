<?php

declare(strict_types=1);

namespace App\Runner;

use App\Runner\DTO\CliArg;
use App\Runner\DTO\CliArgType;
use RuntimeException;

readonly class ParseCliArgs
{
    /** @var array<string, CliArg> */
    public array $options;

    public function __construct(CliArg ...$cliArgs)
    {
        $this->options = $this->parseArgs(...$cliArgs);
    }

    public function getOptions(): Options
    {
        return new Options(
            days: $this->options['day']->value   ?? null,
            parts: $this->options['part']->value ?? null,
            withExamples: (bool) ($this->options['examples']->value ?? false),
            wantsHelp: (bool) ($this->options['help']->value ?? false)
        );
    }

    /** @return array<string, CliArg> */
    protected function parseArgs(CliArg ...$args): array
    {
        // build index of args by long name
        $argsByLongName  = [];
        $argsByShortName = [];
        foreach ($args as $arg) {
            $argsByLongName[$arg->longName] = $arg;
            if ('' !== $arg->shortName) {
                $argsByShortName[$arg->shortName] = $arg;
            }
        }

        // build getopt format strings
        $shortOpts = implode('', array_map(
            fn (CliArg $a) => $a->shortName.$a->type->value,
            array_filter($args, fn (CliArg $a) => '' !== $a->shortName)
        ));
        $longOpts = array_map(fn (CliArg $a) => $a->asGetOpt(), $args);

        // parse cli arguments
        $parsed = getopt($shortOpts, $longOpts);

        // process parsed options
        $result = $argsByLongName;
        foreach ($parsed as $key => $value) {
            // handle both short and long names
            $arg = $argsByLongName[$key] ?? $argsByShortName[$key] ?? null;

            if (null === $arg) {
                throw new RuntimeException("Invalid option: {$key}");
            }

            $processedValue = match ($arg->type) {
                // handle counter-intuitive behaviour of "no value" options being set to false
                // @see: https://www.php.net/manual/en/function.getopt.php#123135
                CliArgType::NO_VALUE => false === $value,
                default              => $this->parseRangeAndCommaSeparated($value)
            };

            $result[$arg->longName] = $arg->withValue($processedValue);
        }

        return $result;
    }

    /** @return array<int, int> */
    protected function parseRangeAndCommaSeparated(string|array|false $input): array
    {
        if (!is_string($input)) {
            return [];
        }

        $result = [];

        // process comma-separated chunks
        foreach (explode(',', $input) as $chunk) {
            $chunk = mb_trim($chunk);

            // handle range (e.g., "1-5" or "Day1-Day5")
            if (str_contains($chunk, '-')) {
                [$start, $end] = explode('-', $chunk, 2);
                $start         = $this->stripDayPrefix($start);
                $end           = $this->stripDayPrefix($end);
                $result        = [...$result, ...range((int) $start, (int) $end)];
            } else {
                // single value (e.g., "17" or "Day17")
                $result[] = (int) $this->stripDayPrefix($chunk);
            }
        }

        sort($result);
        return array_unique($result);
    }

    // strip 'Day' prefix from input (e.g., "Day17" -> "17", "17" -> "17")
    protected function stripDayPrefix(string $value): string
    {
        return preg_replace('/^Day/i', '', mb_trim($value));
    }
}
