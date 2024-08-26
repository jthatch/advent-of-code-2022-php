<?php

declare(strict_types=1);

namespace App\Days;

use App\Contracts\Day;
use Illuminate\Support\Collection;

class Day5 extends Day
{
    public const EXAMPLE1 = <<<eof
            [D]    
        [N] [C]    
        [Z] [M] [P]
         1   2   3 
        
        move 1 from 2 to 1
        move 3 from 1 to 3
        move 2 from 2 to 1
        move 1 from 1 to 2
        eof;

    /**
     * After the rearrangement procedure completes, what crate ends up on top of each stack?
     */
    public function solvePart1(mixed $input): int|string|null
    {
        $input = $this->parseInput($input);
        /** @var Collection $diagram */
        $diagram = $input->get('diagram');
        /** @var Collection $instructions */
        $instructions = $input->get('instructions');

        while ($instructions->isNotEmpty()) {
            $instruction = (string) $instructions->shift();
            preg_match("/move (\d+) from (\d+) to (\d+)/", $instruction, $matches);
            [, $amount, $from, $to] = $matches;
            $amount                 = (int) $amount;
            // move the crates one at a time
            while ($amount > 0) {
                --$amount;
                $crate = $diagram->get($from)->pop();
                $diagram->get($to)->push($crate);
            }
        }

        return collect($diagram)->map(fn ($stack) => $stack->pop())->implode('');
    }

    /**
     * After the rearrangement procedure completes, what crate ends up on top of each stack?
     */
    public function solvePart2(mixed $input): int|string|null
    {
        $input = $this->parseInput($input);
        /** @var Collection $diagram */
        $diagram = $input->get('diagram');
        /** @var Collection $instructions */
        $instructions = $input->get('instructions');

        while ($instructions->isNotEmpty()) {
            $instruction = (string) $instructions->shift();
            preg_match("/move (\d+) from (\d+) to (\d+)/", $instruction, $matches);
            [, $amount, $from, $to] = $matches;
            $amount                 = (int) $amount;
            // move the crates in bulk preserving the original order
            $diagram->get($from)->splice(-$amount)->each(function ($item) use ($diagram, $to) {
                $diagram->get($to)->push($item);
            });
        }

        return collect($diagram)->map(fn ($stack) => $stack->pop())->implode('');
    }

    protected function parseInput(mixed $input): Collection
    {
        $input = is_array($input) ? $input : explode("\n", $input);

        return collect($input)
            ->chunkWhile(fn ($value) => '' !== $value)
            ->map(fn ($chunk) => $chunk->filter(fn ($value) => '' !== $value)->values())
            ->flatMap(fn ($chunk, $index) => 0 === $index
                ? ['diagram' => $this->setupDiagram($chunk)]
                : ['instructions' => collect($chunk)]);
    }

    protected function setupDiagram(Collection $rawDiagram): Collection
    {
        $stackLine = $rawDiagram->pop(); //  " 1   2   3 "
        // create an empty array keyed by the stack columns 1,2,3 etc
        $stacks = array_map(fn () => [], array_flip(preg_split("/\s+/", trim((string) $stackLine))));
        // loop over each remaining diagram line, adding the crates to the correct stack column
        $rawDiagram->each(function ($line) use (&$stacks) {
            $crates = collect(str_split($line, 4))
                ->map(fn ($char) => trim(str_replace(['[', ']'], '', $char)));
            foreach ($stacks as &$stack) {
                $crate = $crates->shift();
                if ('' !== $crate) {
                    $stack[] = $crate;
                }
            }
        });
        foreach ($stacks as &$stack) {
            $stack = collect(array_reverse($stack));
        }

        return collect($stacks);
    }

    public function getExample2(): mixed
    {
        return static::EXAMPLE1;
    }
}
