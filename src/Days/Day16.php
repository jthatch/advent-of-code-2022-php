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
    protected array $valvesToIds         = [];
    protected static array $cache          = [];
    protected array $cache2                = [];

    private const CACHE_FILE = __DIR__ . '/../../day16_cache.php';
    protected bool $saveToCache = false;

    public function solvePart1(mixed $input): int|string|null
    {
        self::$cache     = [];
        $valves          = $this->parseInput($input);
        $this->valves    = $valves->toArray();
        $this->valvesToIds = $valves->pluck('id', 'valve')->toArray();
        $this->flowRates = $valves->pluck('flow_rate', 'valve')->toArray();

        return $this->findMaxPressure('AA', [], 30);
    }

    public function solvePart2(mixed $input): int|string|null
    {
        $valves          = $this->parseInput($input);
        //dump($valves['AA']);
        $this->saveToCache = $valves['AA']['id'] !== 0;
        self::$cache     = [];
        $this->cache2    = $this->saveToCache ? $this->loadCache() : [];
        printf("save to cache: %s, Cache size: %d memory: %d\n", $this->saveToCache ? 'true' : 'false', count($this->cache2), memory_get_usage(true));
        //dd('got here');
        $this->valves    = $valves->toArray();
        $this->valvesToIds = $valves->pluck('id', 'valve')->toArray();
        $this->flowRates = $valves->pluck('flow_rate', 'valve')->toArray();

        $result = $this->findMaxPressureWithElephant('AA', 'AA', [], 26);

        if ($this->saveToCache) {
            $this->saveCache();
        }

        return $result;
    }

    protected function findMaxPressure(string $currentValve, array $openValves, int $remainingTime): int
    {
        sort($openValves);
        $key = "{$currentValve}".implode(',', $openValves)."{$remainingTime}";
        //$key = $this->createBitmaskKey([$currentValve], $openValves, $remainingTime);

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

        $key = $this->createBitmaskKey($sortedValves, $openValves, $remainingTime);

        static $cacheCount = 0;

        static $maxDepthReached = 0;
        static $maxValvesOpened = 0;

        $currentDepth = 26 - $remainingTime;
        $valvesOpenedCount = count($openValves);

        $maxDepthReached = max($maxDepthReached, $currentDepth);
        $maxValvesOpened = max($maxValvesOpened, $valvesOpenedCount);

        if (isset($this->cache2[$key])) {
            return $this->cache2[$key];
        }

        if (0 === $remainingTime) {
            return 0;
        }

        // Early termination check
        $unopenedValves = array_diff(array_keys($this->flowRates), $openValves);
        $potentialPressure = array_sum(array_intersect_key($this->flowRates, array_flip($unopenedValves))) * $remainingTime;
        if ($potentialPressure === 0) {
            $this->cache2[$key] = 0;
            return 0;
        }

        // todo output the total count of $this->cache2 and the number of unique keys
        /* $totalStates = count($this->cache2);
        $uniqueKeys = count(array_unique(array_keys($this->cache2)));
        printf("Total states: %d, Unique keys: %d\n", $totalStates, $uniqueKeys); */

        //$cacheCount++;
        static $lastReportedCount = 0;
        $cacheCount = count($this->cache2);
        if ($this->saveToCache && $cacheCount >= $lastReportedCount + 100000) {
            $depthProgress = ($maxDepthReached / 26) * 100;
            $valveProgress = ($maxValvesOpened / count(array_filter($this->flowRates))) * 100;
            printf(" Cache count: %d (Depth: %.2f%%, Valves: %.2f%%)\n", 
                   $cacheCount, $depthProgress, $valveProgress);
            $this->reportLongRunning();
            $lastReportedCount = $cacheCount - ($cacheCount % 100000);
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

        static $lastSaveTime = 0;
        $currentTime = microtime(true);
        if ($currentTime - $lastSaveTime > 30 && $this->saveToCache) { // Save every 30 seconds
            printf("Saving %d keys to cache\n", count($this->cache2));
            $this->saveCache();
            $lastSaveTime = $currentTime;
        }

        return $maxPressure;
    }

    /**
     * todo fix this
     * create a unique bitmask key for the given valve names, open valves and remaining time
     *
     * @param array $valveNames e.g. ["AA", "BB"]
     * @param array $openValves e.g. ["CC", "DD"]
     * @param integer $remainingTime e.g. 26
     * @return integer
     */

     protected function createBitmaskKey(array $valveNames, array $openValves, int $remainingTime): int
     {
        $bitmask = 0;

        // Encode valve names (2 valves, 7 bits each)
        $bitmask |= ($this->valvesToIds[$valveNames[0]] & 0x7F) << 57;
        $bitmask |= ($this->valvesToIds[$valveNames[1]] & 0x7F) << 50;

        // Encode open valves (up to 50 valves, 1 bit each)
        foreach ($openValves as $openValve) {
            if (isset($this->valvesToIds[$openValve])) {
                $bitmask |= 1 << ($this->valvesToIds[$openValve] & 0x3F);
            }
        }

        // Encode remaining time (5 bits, max 31 minutes)
        $bitmask |= ($remainingTime & 0x1F) << 45;

        return $bitmask;
    }
    
    protected function parseInput(mixed $input): Collection
    {
        $id = 0;
        return collect(is_array($input) ? $input : explode("\n", $input))
            ->mapWithKeys(function (string $line) use (&$id) : array {
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

    private function loadCache(): array
    {
        if (file_exists(self::CACHE_FILE) && $this->saveToCache) {
            $cache = include self::CACHE_FILE;
            return is_array($cache) ? $cache : [];
        }
        return [];
    }

    private function saveCache(): void
    {
        $cacheContent = "<?php\nreturn " . var_export($this->cache2, true) . ";\n";
        file_put_contents(self::CACHE_FILE, $cacheContent);
        //$this->cacheModified = false;
    }
}