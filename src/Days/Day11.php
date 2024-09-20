<?php

declare(strict_types=1);

namespace App\Days;

use App\Contracts\Day;
use Illuminate\Support\Collection;
use RuntimeException;

class Day11 extends Day
{
    public const EXAMPLE1 = <<<eof
        Monkey 0:
            Starting items: 79, 98
            Operation: new = old * 19
            Test: divisible by 23
                If true: throw to monkey 2
                If false: throw to monkey 3

            Monkey 1:
            Starting items: 54, 65, 75, 74
            Operation: new = old + 6
            Test: divisible by 19
                If true: throw to monkey 2
                If false: throw to monkey 0

            Monkey 2:
            Starting items: 79, 60, 97
            Operation: new = old * old
            Test: divisible by 13
                If true: throw to monkey 1
                If false: throw to monkey 3

            Monkey 3:
            Starting items: 74
            Operation: new = old + 3
            Test: divisible by 17
                If true: throw to monkey 0
                If false: throw to monkey 1
    eof;

    /**
     * Figure out which monkeys to chase by counting how many items they inspect over 20 rounds. What is the level of monkey business after 20 rounds of stuff-slinging simian shenanigans?
     */
    public function solvePart1(mixed $input): int|string|null
    {
        $monkeys     = $this->parseInput($input)->toArray();
        $inspections = array_fill_keys(array_keys($monkeys), 0);

        for ($round = 0; $round < 20; ++$round) {
            foreach ($monkeys as $id => &$monkey) {
                $inspections[$id] += count($monkey['items']);
                foreach ($monkey['items'] as $item) {
                    $worry   = intdiv((int) $this->performOperation($item, $monkey['operation']), 3);
                    $throwTo = $monkey[0 === $worry % $monkey['test'] ? 'true' : 'false'];
                    // if worry is divisible by 3, throw to monkey marked as 'true', otherwise monkey marked as 'false'
                    // pass the item to the monkey
                    $monkeys[$throwTo]['items'][] = $worry;
                }
                $monkey['items'] = [];
            }
        }

        // take the two most active monkeys inspected items and multiply together
        return collect($inspections)
            ->sortDesc()
            ->take(2)
            ->reduce(fn ($carry, $item): int => $carry * $item, 1);
    }

    /**
     * What is the level of monkey business after 10000 rounds?
     */
    public function solvePart2(mixed $input): int|string|null
    {
        $monkeys     = $this->parseInput($input)->toArray();
        $inspections = array_fill_keys(array_keys($monkeys), 0);
        // the worry level will quickly grow leading to integer overflows
        // instead calculate the least common multiple to avoid working with massive integers
        $lcm = collect(array_column($monkeys, 'test'))
            ->reduce(fn (int $a, int $b): int => $a * $b / (int) gmp_gcd($a, $b), 1);

        for ($round = 0; $round < 10_000; ++$round) {
            foreach ($monkeys as $id => &$monkey) {
                $inspections[$id] += count($monkey['items']);
                foreach ($monkey['items'] as $item) {
                    $worry = (int) floor($this->performOperation($item, $monkey['operation']));
                    $worry %= $lcm;
                    $throwTo = $monkey[0 === $worry % $monkey['test'] ? 'true' : 'false'];

                    $monkeys[$throwTo]['items'][] = $worry;
                }
                $monkey['items'] = [];
            }
        }

        return collect($inspections)
            ->sortDesc()
            ->take(2)
            ->reduce(fn ($carry, $item): int => $carry * $item, 1);
    }

    /**
     * Inspects an item to determine the worry level by performing a math operation.
     *
     * @param array{target: int|string, symbol: string} $operation
     */
    protected function performOperation(int $old, array $operation): int|float
    {
        $target = 'old' === $operation['target'] ? $old : (int) $operation['target'];

        return '*' === $operation['symbol'] ? $old * $target : $old + $target;
    }

    /**
     * Parse the input string into a Collection of monkey data.
     *
     * @param mixed $input The input string or array to parse
     *
     * @return \Illuminate\Support\Collection<int, array{
     *     id: int,
     *     items: array<int>,
     *     operation: array{symbol: string, target: int|string},
     *     test: int,
     *     true: int,
     *     false: int
     * }>
     */
    protected function parseInput(mixed $input): Collection
    {
        $input = is_array($input) ? $input : explode("\n", $input);

        return collect($input)
            ->map(fn ($line) => trim($line))
            ->chunkWhile(fn ($value) => '' !== $value)
            ->map(fn ($chunk) => $chunk->values())
            ->map(fn ($chunk) => $chunk->filter(fn ($value) => '' !== $value)->values())
            ->map(function (Collection $chunk): array {
                preg_match("/Monkey (\d+):/", $chunk[0], $idMatch);
                preg_match("/Operation: new = old ([*+]) ([\w\d]+)/", $chunk[2], $operationMatch);
                preg_match("/Test: divisible by (\d+)/", $chunk[3], $testMatch);
                preg_match("/If true: throw to monkey (\d+)/", $chunk[4], $trueMatch);
                preg_match("/If false: throw to monkey (\d+)/", $chunk[5], $falseMatch);
                if (!$idMatch || !$operationMatch || !$testMatch || !$trueMatch || !$falseMatch) {
                    throw new RuntimeException('Invalid parsing of puzzle input');
                }

                return [
                    'id'        => (int) $idMatch[1],
                    'items'     => array_map('intval', explode(', ', str_replace('Starting items: ', '', $chunk[1]))),
                    'operation' => [
                        'symbol' => $operationMatch[1],
                        'target' => is_numeric($operationMatch[2]) ? (int) $operationMatch[2] : $operationMatch[2],
                    ],
                    'test'  => (int) $testMatch[1],
                    'true'  => (int) $trueMatch[1],
                    'false' => (int) $falseMatch[1],
                ];
            });
    }
}
