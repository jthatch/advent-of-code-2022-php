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
        $minute = 0;
        $pressure = 0;
        $openValves = [];
        $valve = $input->first();
        $openValve = false;
        while ($minute < 30) {
            ++$minute;
            printf("Minute: %d current pressure: %d current valve: %s open valves: %s\n", $minute, $pressure, $valve['valve'], implode(', ', $openValves));
            if ($openValve) {
                printf("Opening %s releasing %d pressure\n", $openValve['valve'], $openValve['flow_rate']);
                $openValves[] = $openValve['valve'];
                $valve = $openValve;
                $openValve = false;
                continue;
            }

            // otherwise find the next valve with the highest flow rate
            $nextValve = $valve['tunnels']->filter(fn (array $tunnel): bool => !in_array($tunnel['valve'], $openValves))->first();

            if ($valve['flow_rate'] < $nextValve['flow_rate']) {
                printf("Moving to %s\n", $nextValve['valve']);
                $valve = $nextValve;
                $openValves[] = $valve['valve'];
                $open = true;

            } else {
                printf("Opening %s\n", $valve['valve']);
                $openValves[] = $valve['valve'];
                $pressure += $valve['flow_rate'];
                $valve = null;
            }
            $pressure += array_sum(array_map(fn (string $valve): int => $input[$valve]['flow_rate'], $openValves));
            printf("Minute: %d current pressure: %d current valve: %s open valves: %s\n", $minute, $pressure, $valve['valve'], implode(', ', $openValves));
            dd($openValves);
            // take the valve with the highest flow rate by shifting and getting the first key
        }

        dd($pressure);


        return null;
    }

    protected function tunnelWithHighestFlowRate(array $valves, array $openValves): array
    {
        return $valves->filter(fn (array $valve): bool => !in_array($valve['valve'], $openValves))->first();
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
     * 
     * @return Collection<string, array<string, int|Collection<int, string>>>
     */
    protected function parseInput(mixed $input): Collection
    {
        return collect(is_array($input) ? $input : explode("\n", $input))
        ->mapWithKeys(function (string $line): array {
            if (preg_match('/Valve (\w+) has flow rate=(\d+); tunnels? leads? to valves? (.+)/', $line, $matches)) {
                [, $valve, $flowRate, $tunnels] = $matches;
                return [
                    $valve => [
                        'valve'     => $valve,
                        'flow_rate' => (int) $flowRate,
                        'tunnels'   => explode(', ', $tunnels),
                    ]
                ];
            }
            return [];
        });

        /* return $valves->map(function (array $valve) use ($valves) {
            $valve['tunnels'] = collect($valve['tunnels'])
                ->map(fn (string $tunnel): array => ['valve' => $tunnel, 'flow_rate' => $valves[$tunnel]['flow_rate']])
                ->sortByDesc(fn (array $tunnel): int => $tunnel['flow_rate'])
                //->toArray();
                ;

            return $valve;
        }); */
    }
}
