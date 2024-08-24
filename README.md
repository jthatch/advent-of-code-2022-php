## Advent of Code 2022 PHP
The solutions to [advent of code 2022](https://adventofcode.com/2022), solved using PHP 8.2. By [James Thatcher](http://github.com/jthatch)

### Solutions 🥳🎉
> 🎄 [Day 1](/src/Days/Day1.php) 🎅 [Day 2](/src/Days/Day2.php) ☃️ [Day 3](/src/Days/Day3.php) 🦌 [Day 4](/src/Days/Day4.php)
### About
My attempts at tacking the awesome challenges at [Advent of Code 2022](https://adventofcode.com/2022/day/1) using PHP 8.2.

### Commands
_Note: checkout the code then run `make run`. The docker and composer libraries will auto install._

**Solve all days puzzles**  
`make run`

**Solve an individual days puzzles**  
`make run day={N}` e.g. `make run day=13`

**Solve multiple days puzzles**  
`make run day={N},{N1}-{N2}...` e.g. `make run day=1-5,7,10,10,10` _Runs days 1-5, 7 and 10 3 times_

**Solve a single part of a days puzzles**  
`make run day={N} part={N}` e.g. `make run day=16 part=2`

**Create the next days PHP file and download puzzle from server**  
_Auto detects what current Day you are on and will create the next (only if the files don't exist)_
```shell
make next
# Created new file: src/Days/Day8.php
# Fetching latest input using day=8 AOC_COOKIE=53616c7465645f5f539435aCL1P
# ./input/day8.txt downloaded
```

**Use XDebug**  
`make xdebug`

**Xdebug can also be triggered on a single days and/or part**  
`make xdebug day={N}` e.g. `make xdebug day=13` or `make xdebug day=13 part=2`

IDE settings:
- `10000` - xdebug port
- `aoc-2021` - PHP_IDE_CONFIG (what you put in PHPStorm -> settings -> debug -> server -> name)
- `/app` - absolute path on the server
- see [xdebug.ini](/xdebug.ini) if you're stuck