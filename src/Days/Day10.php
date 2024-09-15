<?php

declare(strict_types=1);

namespace App\Days;

use App\Contracts\Day;
use Illuminate\Support\Collection;

class Day10 extends Day
{
    // public const EXAMPLE1  = [self::EXAMPLE1A, self::EXAMPLE1B];
    public const EXAMPLE1A = <<<eof
        noop
        addx 3
        addx -5
        eof;
    public const EXAMPLE1B = <<<eof
        addx 15
        addx -11
        addx 6
        addx -3
        addx 5
        addx -1
        addx -8
        addx 13
        addx 4
        noop
        addx -1
        addx 5
        addx -1
        addx 5
        addx -1
        addx 5
        addx -1
        addx 5
        addx -1
        addx -35
        addx 1
        addx 24
        addx -19
        addx 1
        addx 16
        addx -11
        noop
        noop
        addx 21
        addx -15
        noop
        noop
        addx -3
        addx 9
        addx 1
        addx -3
        addx 8
        addx 1
        addx 5
        noop
        noop
        noop
        noop
        noop
        addx -36
        noop
        addx 1
        addx 7
        noop
        noop
        noop
        addx 2
        addx 6
        noop
        noop
        noop
        noop
        noop
        addx 1
        noop
        noop
        addx 7
        addx 1
        noop
        addx -13
        addx 13
        addx 7
        noop
        addx 1
        addx -33
        noop
        noop
        noop
        addx 2
        noop
        noop
        noop
        addx 8
        noop
        addx -1
        addx 2
        addx 1
        noop
        addx 17
        addx -9
        addx 1
        addx 1
        addx -3
        addx 11
        noop
        noop
        addx 1
        noop
        addx 1
        noop
        noop
        addx -13
        addx -19
        addx 1
        addx 3
        addx 26
        addx -30
        addx 12
        addx -1
        addx 3
        addx 1
        noop
        noop
        noop
        addx -9
        addx 18
        addx 1
        addx 2
        noop
        noop
        addx 9
        noop
        noop
        noop
        addx -1
        addx 2
        addx -37
        addx 1
        addx 3
        noop
        addx 15
        addx -21
        addx 22
        addx -6
        addx 1
        noop
        addx 2
        addx 1
        noop
        addx -10
        noop
        noop
        addx 20
        addx 1
        addx 2
        addx 2
        addx -6
        addx -11
        noop
        noop
        noop
        eof;

    public const EXAMPLE1 = self::EXAMPLE1B;

    // private array $cycles = [3, 5];
    private array $cycles = [20, 60, 100, 140, 180, 220];

    /**
     * Find the signal strength during the 20th, 60th, 100th, 140th, 180th, and 220th cycles. What is the sum of these six signal strengths?
     */
    public function solvePart1(mixed $input): int|string|null
    {
        $x        = 1;
        $cycle    = 0;
        $strength = [];

        foreach ($this->parseInput($input)->toArray() as [$instruction, $value]) {
            ++$cycle;
            if (in_array($cycle, $this->cycles, true)) {
                $strength[$cycle] = $x * $cycle;
            } if ('addx' === $instruction) {
                ++$cycle;
                if (in_array($cycle, $this->cycles, true)) {
                    $strength[$cycle] = $x * $cycle;
                }
            }
            $x += $value;
        }

        return array_sum($strength);
    }

    /**
     * How many positions does the tail of the rope visit at least once?
     */
    public function solvePart2(mixed $input): int|string|null
    {
        $x     = 1;
        $input = $this
            ->parseInput($input)
            ->reduce(function (array $register, array $program) use (&$x) {
                [$instruction, $value] = $program;

                // capture x and cycle at the start/during the command
                [$x, $cycle, $strength ] = $register;

                $isNoop = 'noop' === $instruction;

                // printf("%d. (%s %d) x=%d\n", $cycle, $instruction, $value, $x);

                for ($i = 0; $i < ($isNoop ? 1 : 2); ++$i) {
                    if (in_array($cycle, $this->cycles, true)) {
                        // add the signal strength
                        $strength[$cycle] = $x * $cycle;
                    }
                    // printf("%s %d.%d x=%d\n", $isNoop ? ' ' : '  ', $cycle, $i, $x);
                    ++$cycle;
                }
                if (!$isNoop) {
                    $x += $value; // increment x
                }

                return [$x, $cycle, $strength];
            }, [1, 1, []]); // [x, cycle, strength]

        // dump(['signals' => $input[2]]);
        // dump(['signals' => $input[2], 'x' => $x, 'sum' => collect($input[2])->sum()]);

        return collect($input[2])->sum();
    }

    protected function parseInput(mixed $input): Collection
    {
        $input = is_array($input) ? $input : explode("\n", $input);

        return collect($input)
            ->map(function (string $line) {
                $elements    = explode(' ', $line);
                $elements[1] = (int) ($elements[1] ?? 0);

                return $elements;
            })
        ;
    }
}
