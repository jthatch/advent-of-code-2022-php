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
    protected array $valvesToIds   = [];
    protected static array $cache  = [];
    protected array $distances     = [];
    protected array $nonZeroValves = [];

    public function solvePart1(mixed $input): int|string|null
    {
        self::$cache       = [];
        $valves            = $this->parseInput($input);
        $this->valves      = $valves->toArray();
        $this->valvesToIds = $valves->pluck('id', 'valve')->toArray();
        $this->flowRates   = $valves->pluck('flow_rate', 'valve')->toArray();

        return $this->findMaxPressure('AA', [], 30);
    }

    public function solvePart2(mixed $input): int|string|null
    {
        self::$cache         = [];
        $valves              = $this->parseInput($input);
        $this->valves        = $valves->toArray();
        $this->flowRates     = $valves->pluck('flow_rate', 'valve')->toArray();
        $this->distances     = $this->calculateDistances();
        $this->nonZeroValves = array_values(array_keys(array_filter($this->flowRates, fn ($rate) => $rate > 0)));

        $allValves = (1 << count($this->nonZeroValves)) - 1;
        return $this->dfs('AA', 'AA', $allValves, 26, 26);
    }

    protected function dfs(string $myPos, string $elephantPos, int $remainingValves, int $myTime, int $elephantTime): int
    {
        if ($myTime <= 0 && $elephantTime <= 0) {
            return 0;
        }

        $key = $this->getStateKey($myPos, $elephantPos, $remainingValves, $myTime, $elephantTime);
        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }

        $maxPressure = 0;

        // Optimize: Only consider moves for the actor with more time
        if ($myTime >= $elephantTime) {
            $maxPressure = $this->tryMoves($myPos, $elephantPos, $remainingValves, $myTime, $elephantTime, true);
        } else {
            $maxPressure = $this->tryMoves($elephantPos, $myPos, $remainingValves, $elephantTime, $myTime, false);
        }

        self::$cache[$key] = $maxPressure;
        return $maxPressure;
    }

    protected function tryMoves(string $pos, string $otherPos, int $remainingValves, int $time, int $otherTime, bool $isMe): int
    {
        $maxPressure = 0;
        for ($i = 0; $i < count($this->nonZeroValves); $i++) {
            if (($remainingValves & (1 << $i)) === 0) {
                continue;
            }
            $valve      = $this->nonZeroValves[$i];
            $timeToOpen = $this->distances[$pos][$valve] + 1;

            if ($timeToOpen < $time) {
                $newTime            = $time - $timeToOpen;
                $pressure           = $this->flowRates[$valve] * $newTime;
                $newRemainingValves = $remainingValves & ~(1 << $i);

                $subPressure = $isMe
                    ? $this->dfs($valve, $otherPos, $newRemainingValves, $newTime, $otherTime)
                    : $this->dfs($otherPos, $valve, $newRemainingValves, $otherTime, $newTime);

                $maxPressure = max($maxPressure, $pressure + $subPressure);
            }
        }
        return $maxPressure;
    }

    protected function getStateKey(string $pos1, string $pos2, int $remainingValves, int $time1, int $time2): string
    {
        $pos1Id = array_search($pos1, $this->nonZeroValves);
        $pos2Id = array_search($pos2, $this->nonZeroValves);

        // if the position is not in nonZeroValves (e.g., 'AA'), use a special value
        $pos1Id = false === $pos1Id ? 31 : $pos1Id;
        $pos2Id = false === $pos2Id ? 31 : $pos2Id;

        if ($pos1Id > $pos2Id || ($pos1Id === $pos2Id && $time1 > $time2)) {
            [$pos1Id, $pos2Id, $time1, $time2] = [$pos2Id, $pos1Id, $time2, $time1];
        }

        // use a 64-bit integer (as a string) to store the state
        return sprintf(
            '%d',
            ($pos1Id & 0x1F) | (($pos2Id & 0x1F) << 5) | (($remainingValves & 0xFFFF) << 10) | (($time1 & 0x1F) << 26) | (($time2 & 0x1F) << 31)
        );
    }

    protected function calculateDistances(): array
    {
        $distances = [];
        foreach ($this->valves as $start => $data) {
            $distances[$start] = $this->bfs($start);
        }
        return $distances;
    }

    protected function bfs(string $start): array
    {
        $queue = new SplQueue();
        $queue->enqueue([$start, 0]);
        $visited = [$start => 0];

        while (!$queue->isEmpty()) {
            [$valve, $distance] = $queue->dequeue();

            foreach ($this->valves[$valve]['tunnels'] as $neighbor) {
                if (!isset($visited[$neighbor])) {
                    $visited[$neighbor] = $distance + 1;
                    $queue->enqueue([$neighbor, $distance + 1]);
                }
            }
        }

        return $visited;
    }

    protected function findMaxPressure(string $currentValve, array $openValves, int $remainingTime): int
    {
        sort($openValves);
        $key = "{$currentValve}".implode(',', $openValves)."{$remainingTime}";

        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }

        if (0 === $remainingTime) {
            return 0;
        }

        $maxPressure = 0;

        // try opening the current valve
        if (!in_array($currentValve, $openValves) && $this->flowRates[$currentValve] > 0) {
            $newOpenValves     = [...$openValves, $currentValve];
            $pressureReleased  = $this->flowRates[$currentValve] * ($remainingTime - 1);
            $recursivePressure = $this->findMaxPressure($currentValve, $newOpenValves, $remainingTime - 1);
            $maxPressure       = $pressureReleased + $recursivePressure;
        }

        // try moving to each connected valve
        foreach ($this->valves[$currentValve]['tunnels'] as $nextValve) {
            $pressure    = $this->findMaxPressure($nextValve, $openValves, $remainingTime - 1);
            $maxPressure = max($maxPressure, $pressure);
        }

        self::$cache[$key] = $maxPressure;
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
