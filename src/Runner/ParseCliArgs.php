<?php

declare(strict_types=1);

namespace App\Runner;

use App\Runner\DTO\CliArg;
use App\Runner\DTO\CliArgType;

class ParseCliArgs
{
    /** @var CliARg[] */
    public readonly array $options;

    /**
     * @param CliArg[] $cliArgs
     */
    public function __construct(CliArg ...$cliArgs)
    {
        $this->options = $this->setCliArgs(...$cliArgs);
    }

    public function getOptions(): Options
    {
        return new Options(
            days: $this->options['day']?->value,
            parts: $this->options['part']?->value,
            withExamples: (bool) $this->options['examples']?->value
        );
    }

    protected function setCliArgs(CliArg ...$args): array
    {
        $options       = array_merge(...array_map(fn (CliArg $a) => [$a->longName => $a], $args));
        $longOptions   = array_map(fn (CliArg $a) => $a->asGetOpt(), $options);
        $getOptOptions = getopt('', $longOptions);
        foreach ($getOptOptions as $key => $value) {
            if (!isset($options[$key])) {
                throw new \RuntimeException('Invalid option: '.$key);
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
     */
    protected function parseRangeAndCommaSeparated(string $input): array
    {
        $values = array_map('intval', array_merge([], ...array_map(static fn (string $chunk) => (str_contains($chunk, '-') && [$start, $end] = sscanf($chunk, '%d-%d')) ? range($start, $end) : [$chunk], explode(',', $input))));
        sort($values);

        return $values;
    }

    protected function optionsAsShortOpt(array $longOpts): array
    {
        return array_map(static fn (string $o): string => $o[0].preg_replace('/[a-z]+/', '', $o), $longOpts);
    }
}
