<?php

declare(strict_types=1);

namespace App\Days;

use App\Contracts\Day;
use Illuminate\Support\Collection;
use SplQueue;

class Day16 extends Day
{
    public const EXAMPLE1 = <<<EOF
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
    EOF;

    protected array $flowRates     = [];
    protected array $valves        = [];
    protected array $distances     = [];
    protected array $nonZeroValves = [];
    protected array $valveToIndex  = [];

    // separate caches for part 1 and part 2
    protected static array $part1Cache = [];
    protected static array $part2Cache = [];

    public function solvePart1(mixed $input): int|string|null
    {
        self::$part1Cache = [];
        $this->initializeGraph($input);

        // create bitmask representing all unopened valves
        $allValves = (1 << count($this->nonZeroValves)) - 1;

        return $this->findMaxPressure('AA', $allValves, 30, self::$part1Cache);
    }

    protected function initializeGraph(mixed $input): void
    {
        $valves          = $this->parseInput($input);
        $this->valves    = $valves->toArray();
        $this->flowRates = $valves->pluck('flow_rate', 'valve')->toArray();

        // pre-compute shortest paths between all valve pairs
        $this->distances = $this->calculateDistances();

        // extract only valves with non-zero flow rates
        $this->nonZeroValves = array_values(array_keys(array_filter($this->flowRates, fn ($rate) => $rate > 0)));

        // create index lookup for faster position mapping
        $this->valveToIndex       = array_flip($this->nonZeroValves);
        $this->valveToIndex['AA'] = 31;
    }

    protected function findMaxPressure(string $currentValve, int $remainingValves, int $remainingTime, array &$cache): int
    {
        // base cases: out of time or all valves opened
        if ($remainingTime <= 0 || 0 === $remainingValves) {
            return 0;
        }

        // check memoization cache
        $key = $this->getStateKey($currentValve, $remainingValves, $remainingTime);
        if (isset($cache[$key])) {
            return $cache[$key];
        }

        $maxPressure      = 0;
        $currentDistances = $this->distances[$currentValve];

        // try opening each remaining unopened valve
        foreach ($this->nonZeroValves as $i => $valve) {
            $bit = 1 << $i;

            // skip if this valve is already open (bit is 0)
            if (($remainingValves & $bit) === 0) {
                continue;
            }

            // calculate time to travel and open this valve
            $timeToOpen = $currentDistances[$valve] + 1;

            // only proceed if we have enough time left
            if ($timeToOpen < $remainingTime) {
                $newTime  = $remainingTime - $timeToOpen;
                $pressure = $this->flowRates[$valve] * $newTime;

                // mark this valve as opened by flipping its bit to 0
                $newRemainingValves = $remainingValves & ~$bit;

                $subPressure = $this->findMaxPressure($valve, $newRemainingValves, $newTime, $cache);
                $maxPressure = max($maxPressure, $pressure + $subPressure);
            }
        }

        return $cache[$key] = $maxPressure;
    }

    protected function getStateKey(string $pos, int $remainingValves, int $time): int
    {
        // use pre-computed index lookup
        $posId = $this->valveToIndex[$pos];

        // pack state into single integer: pos(5 bits) | valves(16 bits) | time(5 bits)
        return ($posId & 0x1F) | (($remainingValves & 0xFFFF) << 5) | (($time & 0x1F) << 21);
    }

    protected function calculateDistances(): array
    {
        // compute all-pairs shortest paths using bfs from each valve
        $distances = [];
        foreach ($this->valves as $start => $data) {
            $distances[$start] = $this->bfs($start);
        }
        return $distances;
    }

    protected function bfs(string $start): array
    {
        // breadth-first search to find shortest path from start to all other valves
        $queue   = new SplQueue();
        $visited = [$start => 0];
        $queue->enqueue([$start, 0]);

        while (!$queue->isEmpty()) {
            /** @var array{string, int} $item */
            $item               = $queue->dequeue();
            [$valve, $distance] = $item;

            foreach ($this->valves[$valve]['tunnels'] as $neighbor) {
                if (!isset($visited[$neighbor])) {
                    $visited[$neighbor] = $distance + 1;
                    $queue->enqueue([$neighbor, $distance + 1]);
                }
            }
        }

        return $visited;
    }

    public function solvePart2(mixed $input): int|string|null
    {
        self::$part2Cache = [];
        $this->initializeGraph($input);

        // compute best score for each possible subset of valves (one actor working alone)
        $allValves   = (1 << count($this->nonZeroValves)) - 1;
        $scores      = [];
        $maxPressure = 0;

        // only iterate through half the subsets due to symmetry
        $halfMasks = 1 << (count($this->nonZeroValves) - 1);

        for ($mask = 0; $mask <= $halfMasks; $mask++) {
            // compute score for this subset
            if (!isset($scores[$mask])) {
                $scores[$mask] = $this->findMaxPressure('AA', $mask, 26, self::$part2Cache);
            }

            // compute complement subset
            $complement = $allValves ^ $mask;
            if (!isset($scores[$complement])) {
                $scores[$complement] = $this->findMaxPressure('AA', $complement, 26, self::$part2Cache);
            }

            // check if this partition is better than current best
            $total       = $scores[$mask] + $scores[$complement];
            $maxPressure = max($maxPressure, $total);
        }

        return $maxPressure;
    }

    protected function parseInput(mixed $input): Collection
    {
        $id = 0;
        return collect(is_array($input) ? $input : explode("\n", $input))
            ->mapWithKeys(function (string $line) use (&$id): array {
                if (preg_match('/Valve (\w+) has flow rate=(\d+); tunnels? leads? to valves? (.+)/', $line, $matches)) {
                    [, $valve, $flowRate, $tunnels] = $matches;
                    return [
                        $valve => [
                            'id'        => $id++,
                            'valve'     => $valve,
                            'flow_rate' => (int) $flowRate,
                            'tunnels'   => explode(', ', $tunnels),
                        ]
                    ];
                }
                return [];
            });
    }
}
