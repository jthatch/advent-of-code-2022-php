<?php

declare(strict_types=1);

namespace App\Days;

use App\Contracts\Day;
use Illuminate\Support\Collection;

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

    protected array $flowRates             = [];
    protected array $valves                = [];
    protected static array $cache          = [];
    protected array $cache2                = [];
    protected const MAX_CACHE_SIZE         = 1000000; // Reduced cache size
    private const CACHE_EVICTION_THRESHOLD = 900000; // Threshold for cache cleanup

    public function solvePart1(mixed $input): int|string|null
    {
        self::$cache     = [];
        $valves          = $this->parseInput($input);
        $this->valves    = $valves->toArray();
        $this->flowRates = $valves->pluck('flow_rate', 'valve')->toArray();

        return $this->findMaxPressure('AA', [], 30);
    }

    public function solvePart2(mixed $input): int|string|null
    {
        $this->cache2    = [];
        $valves          = $this->parseInput($input);
        $this->valves    = $valves->toArray();
        $this->flowRates = $valves->pluck('flow_rate', 'valve')->toArray();

        return $this->findMaxPressureWithElephant('AA', 'AA', [], 26);
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

        // Try opening the current valve
        if (!in_array($currentValve, $openValves) && $this->flowRates[$currentValve] > 0) {
            $newOpenValves     = [...$openValves, $currentValve];
            $pressureReleased  = $this->flowRates[$currentValve] * ($remainingTime - 1);
            $recursivePressure = $this->findMaxPressure($currentValve, $newOpenValves, $remainingTime - 1);
            $maxPressure       = $pressureReleased + $recursivePressure;
        }

        // Try moving to each connected valve
        foreach ($this->valves[$currentValve]['tunnels'] as $nextValve) {
            $pressure    = $this->findMaxPressure($nextValve, $openValves, $remainingTime - 1);
            $maxPressure = max($maxPressure, $pressure);
        }

        self::$cache[$key] = $maxPressure;
        return $maxPressure;
    }

    protected function findMaxPressureWithElephant(
        string $humanValve,
        string $elephantValve,
        array $openValves,
        int $remainingTime
    ): int {
        sort($openValves);
        $sortedValves = [$humanValve, $elephantValve];
        sort($sortedValves);
        $key = implode('', $sortedValves).implode(',', $openValves)."{$remainingTime}";

        static $cacheCount = 0;
        $cacheCount++;
        if (0 === $cacheCount % 100000) {
            printf("Cache count: %d\r", $cacheCount);
        }

        if (isset($this->cache2[$key])) {
            return $this->cache2[$key];
        }

        if (0 === $remainingTime) {
            return 0;
        }

        $maxPressure = 0;

        // Both open valves
        if ($humanValve !== $elephantValve && !in_array($humanValve, $openValves) && $this->flowRates[$humanValve] > 0 && !in_array($elephantValve, $openValves) && $this->flowRates[$elephantValve] > 0) {
            $newOpenValves     = [...$openValves, $humanValve, $elephantValve];
            $pressureReleased  = ($this->flowRates[$humanValve] + $this->flowRates[$elephantValve]) * ($remainingTime - 1);
            $recursivePressure = $this->findMaxPressureWithElephant(
                $humanValve,
                $elephantValve,
                $newOpenValves,
                $remainingTime - 1
            );
            $maxPressure = max($maxPressure, $pressureReleased + $recursivePressure);
        }

        // Human opens valve, elephant moves
        if (!in_array($humanValve, $openValves) && $this->flowRates[$humanValve] > 0) {
            $newOpenValves    = [...$openValves, $humanValve];
            $pressureReleased = $this->flowRates[$humanValve] * ($remainingTime - 1);
            foreach ($this->valves[$elephantValve]['tunnels'] as $nextElephantValve) {
                $recursivePressure = $this->findMaxPressureWithElephant(
                    $humanValve,
                    $nextElephantValve,
                    $newOpenValves,
                    $remainingTime - 1
                );
                $maxPressure = max($maxPressure, $pressureReleased + $recursivePressure);
            }
        }

        // Elephant opens valve, human moves
        if (!in_array($elephantValve, $openValves) && $this->flowRates[$elephantValve] > 0) {
            $newOpenValves    = [...$openValves, $elephantValve];
            $pressureReleased = $this->flowRates[$elephantValve] * ($remainingTime - 1);
            foreach ($this->valves[$humanValve]['tunnels'] as $nextHumanValve) {
                $recursivePressure = $this->findMaxPressureWithElephant(
                    $nextHumanValve,
                    $elephantValve,
                    $newOpenValves,
                    $remainingTime - 1
                );
                $maxPressure = max($maxPressure, $pressureReleased + $recursivePressure);
            }
        }

        // Both move
        foreach ($this->valves[$humanValve]['tunnels'] as $nextHumanValve) {
            foreach ($this->valves[$elephantValve]['tunnels'] as $nextElephantValve) {
                $pressure = $this->findMaxPressureWithElephant(
                    $nextHumanValve,
                    $nextElephantValve,
                    $openValves,
                    $remainingTime - 1
                );
                $maxPressure = max($maxPressure, $pressure);
            }
        }

        // Cache the result with more aggressive eviction strategy
        $this->cache2[$key] = $maxPressure;

        /* if (count(self::$cache) > self::CACHE_EVICTION_THRESHOLD) {
            $this->cleanupCache();
        } */

        return $maxPressure;
    }

    private function cleanupCache(): void
    {
        $cacheSize     = count(self::$cache);
        $itemsToRemove = $cacheSize - self::MAX_CACHE_SIZE / 2;

        if ($itemsToRemove > 0) {
            $keys = array_rand(self::$cache, $itemsToRemove);
            foreach ($keys as $key) {
                unset(self::$cache[$key]);
            }
        }
    }

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
    }
}
