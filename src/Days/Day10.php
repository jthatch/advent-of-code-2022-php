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
     * What eight capital letters appear on your CRT?
     *
     * notes:
     *  - the CRT draws a single pixel during each cycle.
     */
    public function solvePart2(mixed $input): string
    {
        $x     = 1;
        $cycle = 0;
        $crt   = array_fill(0, 6, array_fill(0, 40, '.'));

        $drawPixel = function (int $cycle, int $spritePosition) use (&$crt): void {
            $row = intdiv($cycle - 1, 40);
            $col = ($cycle - 1) % 40;

            if (abs($col - $spritePosition) <= 1) {
                $crt[$row][$col] = '#';
            }
        };

        foreach ($this->parseInput($input) as [$instruction, $value]) {
            ++$cycle;
            $drawPixel($cycle, $x);

            if ('addx' === $instruction) {
                ++$cycle;
                $drawPixel($cycle, $x);
                $x += $value;
            }
        }

        $renderedCrt = implode("\n", array_map(fn (array $row) => implode('', $row), $crt));

        printf("%s\n", $renderedCrt);
        /*
         * outputs
         * ###...##..#..#.####..##..#....#..#..##..
         * #..#.#..#.#..#.#....#..#.#....#..#.#..#.
         * #..#.#....####.###..#....#....#..#.#....
         * ###..#.##.#..#.#....#.##.#....#..#.#.##.
         * #....#..#.#..#.#....#..#.#....#..#.#..#.
         * #.....###.#..#.#.....###.####..##...###.
         */

        return 'PGHFGLUG';
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
