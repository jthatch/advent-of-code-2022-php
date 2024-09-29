<?php

declare(strict_types=1);

namespace App\Days;

use App\Contracts\Day;
use Illuminate\Support\Collection;

class Day16 extends Day
{
    public const EXAMPLE1 = <<<eof
    Valve AA has flow rate=0; tunnels lead to valves DD, II, BB
    Valve BB has flow rate=13; tunnels lead to valves CC, AA
    Valve CC has flow rate=2; tunnels lead to valves DD, BB
    Valve DD has flow rate=20; tunnels lead to valves CC, AA, EE
    Valve EE has flow rate=3; tunnels lead to valves FF, DD
    Valve FF has flow rate=0; tunnels lead to valves EE, GG
    Valve GG has flow rate=0; tunnels lead to valves FF, HH
    Valve HH has flow rate=22; tunnel leads to valve GG
    Valve II has flow rate=0; tunnels lead to valves AA, JJ
    Valve JJ has flow rate=21; tunnel leads to valve II
    eof;

    /**
     * Solve Part 1 of the day's problem.
     */
    public function solvePart1(mixed $input): int|string|null
    {
        $input = $this->parseInput($input);
        dd($input);

        return null;
    }

    /**
     * Solve Part 2 of the day's problem.
     */
    public function solvePart2(mixed $input): int|string|null
    {
        $input = $this->parseInput($input);

        // todo: implement solution for Part 2

        return null;
    }

    /**
     * Parse the input data.
     * @return Collection<int, array<string, string|int|array<int, string>>>
     */
    protected function parseInput(mixed $input): Collection
    {
        $input = is_array($input) ? $input : explode("\n", $input);

        return collect($input)
            ->map(function (string $line): array {
                $valve    = null;
                $flowRate = null;
                $tunnels  = null;
                if (preg_match('/Valve (\w+) has flow rate=(\d+); tunnels? leads? to valves? (.+)/', $line, $matches)) {
                    $valve    = $matches[1];
                    $flowRate = (int)$matches[2];
                    $tunnels  = explode(', ', $matches[3]);
                }
                return [
                    'valve'     => $valve,
                    'flow_rate' => $flowRate,
                    'tunnels'   => $tunnels,
                ];
            })
        ;
    }
}
