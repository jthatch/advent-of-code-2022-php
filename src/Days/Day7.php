<?php

declare(strict_types=1);

namespace App\Days;

use App\Contracts\Day;
use Illuminate\Support\Collection;

class Day7 extends Day
{
    public const EXAMPLE1 = <<<eof
        $ cd /
        $ ls
        dir a
        14848514 b.txt
        8504156 c.dat
        dir d
        $ cd a
        $ ls
        dir e
        29116 f
        2557 g
        62596 h.lst
        $ cd e
        $ ls
        584 i
        $ cd ..
        $ cd ..
        $ cd d
        $ ls
        4060174 j
        8033020 d.log
        5626152 d.ext
        7214296 k
        eof;

    /**
     * Find all of the directories with a total size of at most 100000. What is the sum of the total sizes of those directories?
     */
    public function solvePart1(mixed $input): int|string|null
    {
        return $this->buildFilesystem($this->parseInput($input))
            // find any directories smaller than 100k
            ->filter(fn (array $dir): bool => $dir['size'] < 100000)
            ->sum(fn (array $dir): int => $dir['size']);
    }

    /**
     * Find the smallest directory that, if deleted, would free up enough space on the filesystem to run the update. What is the total size of that directory?
     */
    public function solvePart2(mixed $input): int|string|null
    {
        $fs            = $this->buildFilesystem($this->parseInput($input));
        $totalSize     = 70000000;
        $minSize       = 30000000;
        $availableSize = $totalSize - $fs['/']['size'];
        $requiredSize  = $minSize   - $availableSize;
        $potential     = $fs
            ->filter(fn (array $dir): bool => $dir['size'] >= $requiredSize)
            ->sortBy(fn (array $dir): int => $dir['size']);

        return $potential->first()['size'];
    }

    /**
     * Builds a filesystem representation based on the given input collection.
     *
     * @param Collection $io the input collection containing commands and their output
     *
     * @return Collection The filesystem representation as an associative array wrapped in a collection.
     *
     * e.g. ['/' => ['size' => 1000], '/a' => ['size' => 600], '/b' => ['size' => 400]]
     */
    protected function buildFilesystem(Collection $io): Collection
    {
        $fs = [];
        // we start by looping over a batch of commands that can contain output
        $currentDir = '';
        $io->each(function (Collection $chunk) use (&$fs, &$currentDir): void {
            // process cd commands
            if (str_starts_with($chunk[0] ?? '', '$ cd ')) {
                preg_match('/\$ cd ([a-z.\/]+)$/', $chunk[0], $match);
                $currentDir = match ($match[1] ?? null) {
                    '/'     => '/',
                    '..'    => dirname($currentDir),
                    null    => $currentDir,
                    default => sprintf(
                        '%s%s%s',
                        $currentDir,
                        '/' === $currentDir ? '' : '/',
                        $match[1]
                    ),
                };
                $fs[$currentDir] ??= [
                    'files' => [],
                    'dirs'  => [],
                    'size'  => null,
                ];
            // process list
            } elseif (str_starts_with($chunk[0] ?? '', '$ ls')) {
                // remove the command and extract files and folders
                $chunk->shift();
                $chunk->map(function ($line) use (&$fs, &$currentDir) {
                    [$typeOrSize, $name] = explode(' ', $line);
                    if ('dir' === $typeOrSize) {
                        $fs[$currentDir]['dirs'][] = $name;
                    } else {
                        $fs[$currentDir]['files'][$name] = (int) $typeOrSize;
                    }
                });
            }
        });

        // recursively set the size
        $this->calculateSize($fs, '/');

        return collect($fs);
    }

    /**
     * Calculates the total size of a directory and all its subdirectories.
     *
     * @param array  $fs         a reference to the filesystem array
     * @param string $currentDir the path of the current directory
     *
     * @return int the total size of the directory and its subdirectories
     */
    protected function calculateSize(array &$fs, string $currentDir): int
    {
        if (null !== $fs[$currentDir]['size']) {
            return $fs[$currentDir]['size'];
        }

        $size = collect($fs[$currentDir]['files'])->sum();
        foreach ($fs[$currentDir]['dirs'] as $dir) {
            $subDir = '/' === $currentDir ? "/$dir" : "$currentDir/$dir";
            $size += $this->calculateSize($fs, $subDir);
        }
        $fs[$currentDir]['size'] = $size;

        return $size;
    }

    protected function parseInput(mixed $input): Collection
    {
        $input = is_array($input) ? $input : explode("\n", $input);

        return collect($input)
            // take command and output into chunks for easier processing
            ->chunkWhile(fn ($line) => !str_starts_with($line, '$ '))
            ->map(fn ($chunk) => $chunk->values())
        ;
    }
}
