<?php

declare(strict_types=1);

namespace App\Days;

use App\Contracts\Day;
use Illuminate\Support\Collection;

class Day15 extends Day
{
    public const EXAMPLE1 = <<<eof
    Sensor at x=2, y=18: closest beacon is at x=-2, y=15
    Sensor at x=9, y=16: closest beacon is at x=10, y=16
    Sensor at x=13, y=2: closest beacon is at x=15, y=3
    Sensor at x=12, y=14: closest beacon is at x=10, y=16
    Sensor at x=10, y=20: closest beacon is at x=10, y=16
    Sensor at x=14, y=17: closest beacon is at x=10, y=16
    Sensor at x=8, y=7: closest beacon is at x=2, y=10
    Sensor at x=2, y=0: closest beacon is at x=2, y=10
    Sensor at x=0, y=11: closest beacon is at x=2, y=10
    Sensor at x=20, y=14: closest beacon is at x=25, y=17
    Sensor at x=17, y=20: closest beacon is at x=21, y=22
    Sensor at x=16, y=7: closest beacon is at x=15, y=3
    Sensor at x=14, y=3: closest beacon is at x=15, y=3
    Sensor at x=20, y=1: closest beacon is at x=15, y=3
    eof;

    /**
     * Solve Part 1 of the day's problem.
     */
    public function solvePart1(mixed $input): int|string|null
    {
        $input   = $this->parseInput($input);
        $targetY = 18 === $input->first()['sensor']['y']
            ? 10        // for the example input
            : 2000000; // for the actual puzzle input

        $ranges = $input->map(function ($sensor) use ($targetY) {
            $distance         = abs($sensor['sensor']['x'] - $sensor['beacon']['x']) + abs($sensor['sensor']['y'] - $sensor['beacon']['y']);
            $distanceToTarget = abs($sensor['sensor']['y'] - $targetY);
            $xRange           = $distance - $distanceToTarget;

            return $xRange >= 0 ? [$sensor['sensor']['x'] - $xRange, $sensor['sensor']['x'] + $xRange] : null;
        })->filter()->values();

        // sort ranges by start position
        $ranges = $ranges->sortBy(fn ($range) => $range[0]);

        // merge overlapping ranges
        $mergedRanges = $ranges->reduce(function ($carry, $range) {
            if ($carry->isEmpty()) {
                $carry->push($range);
            } else {
                $last = $carry->last();
                if ($range[0] <= $last[1] + 1) {
                    $carry->pop();
                    $carry->push([$last[0], max($last[1], $range[1])]);
                } else {
                    $carry->push($range);
                }
            }
            return $carry;
        }, collect([]));

        // count positions that cannot contain a beacon
        $count = $mergedRanges->sum(fn ($range) => $range[1] - $range[0] + 1);

        // subtract beacons already on the target row
        $beaconsOnTargetRow = $input->pluck('beacon')
            ->filter(fn ($beacon) => $beacon['y'] === $targetY)
            ->pluck('x')
            ->unique()
            ->filter(fn ($x) => $mergedRanges->contains(fn ($range) => $x >= $range[0] && $x <= $range[1]));

        $count -= $beaconsOnTargetRow->count();

        return $count;
    }


    /**
     * Solve Part 2 of the day's problem.
     * 7.0s to 0.1s performance improvements:
     * - avoid using a 2d array and instead use a more efficient isValidPoint method to check if a point is valid
     * - check the four points just outside the diamond shape of the sensor's range
     * - avoid looping through the entire grid by checking only the perimeter of each sensor's range
     */
    public function solvePart2(mixed $input): int|string|null
    {
        $input = $this->parseInput($input);
        $max   = 18 === $input->first()['sensor']['y']
            ? 20        // for the example input
            : 4000000; // for the actual puzzle input

        // create an array of sensors with their x, y, and distance to the closest beacon (manhattan distance)
        $sensors = $input->map(fn ($sensor) => [
            'x'        => $sensor['sensor']['x'],
            'y'        => $sensor['sensor']['y'],
            'distance' => abs($sensor['sensor']['x'] - $sensor['beacon']['x']) + abs($sensor['sensor']['y'] - $sensor['beacon']['y']),
        ])->toArray();

        // horizontal, vertical, and diagonal points just outside the diamond shape of the sensor's range
        $points = [[-1, -1], [-1, 1], [1, -1], [1, 1]];

        // check the perimeter of each sensor's range
        foreach ($sensors as $sensor) {
            // check points just outside the diamond shape of the sensor's range
            for ($dx = 0; $dx <= $sensor['distance'] + 1; $dx++) {
                $dy = $sensor['distance'] + 1 - $dx;

                foreach ($points as [$signX, $signY]) {
                    $x = $sensor['x'] + $dx * $signX;
                    $y = $sensor['y'] + $dy * $signY;

                    // check if the point is within the bounds of the grid
                    if ($x >= 0 && $x <= $max && $y >= 0 && $y <= $max) {
                        if ($this->isValidPoint($x, $y, $sensors)) {
                            // return the tuning frequency
                            return $x * 4000000 + $y;
                        }
                    }
                }
            }
        }

        return null;
    }

    /**
     * check if a point is valid by ensuring it's outside the range of all sensors.
     *
     * @param int $x
     * @param int $y
     * @param array $sensors sensors with their x, y, and distance to the closest beacon
     * @return bool
     */
    protected function isValidPoint(int $x, int $y, array $sensors): bool
    {
        foreach ($sensors as $sensor) {
            $distance = abs($x - $sensor['x']) + abs($y - $sensor['y']);
            if ($distance <= $sensor['distance']) {
                return false;
            }
        }
        return true;
    }

    /**
     * Parse the input data.
     */
    protected function parseInput(mixed $input): Collection
    {
        $input = is_array($input) ? $input : explode("\n", $input);

        return collect($input)
            ->map(function (string $line): array {
                $sensorX = $sensorY = $beaconX = $beaconY = null;
                sscanf($line, "Sensor at x=%d, y=%d: closest beacon is at x=%d, y=%d", $sensorX, $sensorY, $beaconX, $beaconY);
                return [
                    'sensor' => ['x' => (int) $sensorX, 'y' => (int) $sensorY],
                    'beacon' => ['x' => (int) $beaconX, 'y' => (int) $beaconY],
                ];
            });
    }
}
