# Advent of Code 2022 - PHP

This repository contains PHP solutions for the [Advent of Code 2022](https://adventofcode.com/2022) challenges.

## Requirements

- Docker
- Bash shell

## Setup

1. Clone this repository
2. Make sure the script is executable: `chmod +x aoc.sh`
3. Run `./aoc.sh build` to build the Docker image
4. Run `./aoc.sh composer` to install dependencies

## Usage

The `aoc.sh` script provides a wrapper around Docker to run the PHP code. It allows you to pass command-line arguments directly to the PHP script.

### Basic Commands

```bash
# Run all days
./aoc.sh run

# Run a specific day
./aoc.sh run --day=1

# Run a specific day with examples
./aoc.sh run --day=15 --examples

# Run multiple days
./aoc.sh run --day=1-5,9

# Run a specific part of a day
./aoc.sh run --day=6,7 --part=2

# Show help for the run.php script
./aoc.sh run --help
```

### Other Commands

```bash
# Build the Docker image
./aoc.sh build

# Launch a shell into the Docker container
./aoc.sh shell

# Run composer commands
./aoc.sh composer update
./aoc.sh composer require package/name

# Run with xdebug enabled
./aoc.sh xdebug --day=1

# Run with xdebug profiler
./aoc.sh xdebug-profile

# Run PHP CS Fixer
./aoc.sh pint

# Run PHPStan
./aoc.sh phpstan

# Retrieve the latest day's input from server
./aoc.sh get-input

# Create next day's file
./aoc.sh next

# Show help
./aoc.sh help
```

## Getting Input Files

To retrieve input files from the Advent of Code website, you need to set the `AOC_COOKIE` environment variable with your session cookie:

```bash
export AOC_COOKIE=your_session_cookie_here
./aoc.sh get-input
```

## Advantages Over the Previous Makefile Approach

1. **Direct Argument Passing**: All arguments after the `run` command are passed directly to the PHP script, allowing for commands like `./aoc.sh run --help`.
2. **Simpler Syntax**: The command structure is more intuitive and follows standard CLI patterns.
3. **Better Help Documentation**: Comprehensive help is available with `./aoc.sh help`.
4. **Easier Maintenance**: The script is more modular and easier to extend with new functionality.

## License

[MIT License](LICENSE)