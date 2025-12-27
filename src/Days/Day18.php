<?php

declare(strict_types=1);

namespace App\Days;

use App\Contracts\Day;
use Illuminate\Support\Collection;

class Day18 extends Day
{
    public const EXAMPLE1 = <<<EOF
    2,2,2
    1,2,2
    3,2,2
    2,1,2
    2,3,2
    2,2,1
    2,2,3
    2,2,4
    2,2,6
    1,2,5
    3,2,5
    2,1,5
    2,3,5
    EOF;

    /**
     * Solve Part 1 of the day's problem.
     */
    public function solvePart1(mixed $input): int|string|null
    {
        $input = $this->parseInput($input);

        $cubeCount   = $input->count();
        $surfaceArea = $cubeCount * 6;  // 3d cubes have 6 sides

        // populate the grid with the cubes
        $grid = $input->reduce(function (array $grid, array $coordinates) {
            $grid[$coordinates[0]][$coordinates[1]][$coordinates[2]] = true;

            return $grid;
        }, []);

        // now loop over each of the 3d cubes again and check if the neighbours are set
        // if so, for each connection we subtract 1 from the surface area
        $input->each(function (array $coordinates) use ($grid, &$surfaceArea): void {
            [$x, $y, $z] = $coordinates;

            // check all 6 neighbours
            $neighbors = [
                [$x + 1, $y, $z], [$x - 1, $y, $z],
                [$x, $y + 1, $z], [$x, $y - 1, $z],
                [$x, $y, $z + 1], [$x, $y, $z - 1],
            ];

            foreach ($neighbors as [$nx, $ny, $nz]) {
                if (isset($grid[$nx][$ny][$nz])) {
                    $surfaceArea--;
                }
            }
        });

        return $surfaceArea;
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
     * Parse the input data. returns [x, y, z]
     */
    protected function parseInput(mixed $input): Collection
    {
        $input = is_array($input) ? $input : explode("\n", $input);

        return collect($input)
            ->map(fn (string $coordinates) => array_map('intval', explode(',', $coordinates)))
        ;
    }
}
