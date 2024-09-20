<?php

declare(strict_types=1);

namespace App\Runner;

use App\Runner\DTO\CliArg;
use App\Runner\DTO\CliArgType;
use RuntimeException;

class ParseCliArgs
{
    /**
     * @var array<string, CliArg>
     */
    public readonly array $options;

    public function __construct(CliArg ...$cliArgs)
    {
        $this->options = $this->setCliArgs(...$cliArgs);
    }

    public function getOptions(): Options
    {
        return new Options(
            days: $this->options['day']->value   ?? null,
            parts: $this->options['part']->value ?? null,
            withExamples: (bool) ($this->options['examples']->value ?? false),
        );
    }

    /**
     * @param CliArg ...$args
     *
     * @return array<string, CliArg>
     */
    protected function setCliArgs(CliArg ...$args): array
    {
        $options       = array_merge(...array_map(fn (CliArg $a) => [$a->longName => $a], $args));
        $longOptions   = array_map(fn (CliArg $a) => $a->asGetOpt(), $options);
        $getOptOptions = getopt('', $longOptions);
        foreach ($getOptOptions as $key => $value) {
            if (!isset($options[$key])) {
                throw new RuntimeException('Invalid option: '.$key);
            }

            $option = &$options[$key];
            if (CliArgType::NO_VALUE === $option->type) {
                // handle counter-intuitive behaviour of "no value" options being set to false
                // @see: https://www.php.net/manual/en/function.getopt.php#123135
                $option->setValue(false === $value);
            } else {
                $option->setValue($this->parseRangeAndCommaSeparated($value));
            }
        }

        return $options;
    }

    /**
     * Returns an array containing the unique values found in the comma-separated and ranged list, including combinations of both
     * Example: "1-3,5" would result in [1,2,3,5].
     *
     * @param string|array<int, mixed>|false $input
     * @return array<int, int>
     */
    protected function parseRangeAndCommaSeparated(string|array|false $input): array
    {
        if (!is_string($input)) {
            return [];
        }

        $values = array_map('intval', array_merge([], ...array_map(function (string $chunk) {
            if (str_contains($chunk, '-')) {
                $parts = explode('-', $chunk, 2);
                $start = (int) $parts[0];
                $end   = (int) ($parts[1] ?? $start);
                return range($start, $end);
            }
            return [$chunk];
        }, explode(',', $input))));
        sort($values);

        return $values;
    }

    /**
     * @param array<string> $longOpts
     *
     * @return array<string>
     */
    protected function optionsAsShortOpt(array $longOpts): array
    {
        return array_map(static fn (string $o): string => $o[0].preg_replace('/[a-z]+/', '', $o), $longOpts);
    }
}
