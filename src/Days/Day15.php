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
     */
    public function solvePart2(mixed $input): int|string|null
    {
        $input = $this->parseInput($input);
        $max   = 18 === $input->first()['sensor']['y']
            ? 20        // for the example input
            : 4000000; // for the actual puzzle input

        $sensors = $input->map(fn ($sensor) => [
            'x'        => $sensor['sensor']['x'],
            'y'        => $sensor['sensor']['y'],
            'distance' => abs($sensor['sensor']['x'] - $sensor['beacon']['x']) + abs($sensor['sensor']['y'] - $sensor['beacon']['y']),
        ])->toArray();

        // for each row, check if there is a gap in the coverage
        for ($y = 0; $y <= $max; $y++) {
            $ranges = [];
            foreach ($sensors as $sensor) {
                $dy = abs($sensor['y'] - $y);
                // if the row is within the sensor's range, add the range to the list
                if ($dy <= $sensor['distance']) {
                    $dx       = $sensor['distance'] - $dy;
                    $ranges[] = [$sensor['x'] - $dx, $sensor['x'] + $dx];
                }
            }

            // sort ranges by start position
            usort($ranges, fn ($a, $b) => $a[0] <=> $b[0]);

            // check for gaps in the coverage
            $x = 0;
            foreach ($ranges as $range) {
                if ($x < $range[0]) {
                    // found the gap
                    return $x * 4000000 + $y;
                }
                $x = max($x, $range[1] + 1);
                if ($x > $max) {
                    // out of bounds
                    break;
                }
            }

            if ($x <= $max) {
                // found the gap
                return $x * 4000000 + $y;
            }
        }

        return null;
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
