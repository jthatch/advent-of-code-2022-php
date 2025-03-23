#!/bin/bash

# Advent of Code 2022 - Docker wrapper script
# This script replaces the Makefile approach to allow for direct passing of arguments

# Configuration
IMAGE_NAME="aoc-2022"
UID=$(id -u)
GID=$(id -g)
PHP_TWEAKS="-dmemory_limit=1G -dopcache.enable_cli=1 -dopcache.jit_buffer_size=100M -dopcache.jit=1255"

# Function to display help
show_help() {
    echo -e "\033[32m---------------------------------------------------------------------------"
    echo -e "  Advent of Code 2022 - James Thatcher"
    echo -e "---------------------------------------------------------------------------\033[0m"
    echo ""
    echo "Usage: ./aoc.sh [command] [options]"
    echo ""
    echo "Commands:"
    echo "  run [options]        Run the PHP script with options passed directly to run.php"
    echo "  build                Build the Docker image"
    echo "  shell                Launch a shell into the Docker container"
    echo "  composer [cmd]       Run composer commands (default: update)"
    echo "  xdebug [options]     Run with xdebug enabled"
    echo "  xdebug-profile       Run with xdebug profiler"
    echo "  pint                 Run PHP CS Fixer"
    echo "  phpstan              Run PHPStan"
    echo "  get-input            Retrieve the latest day's input from server"
    echo "  next                 Create next day's file"
    echo "  help                 Show this help message"
    echo ""
    echo "Examples:"
    echo "  ./aoc.sh run --day=15 --examples"
    echo "  ./aoc.sh run --day=1-5,9"
    echo "  ./aoc.sh run --day=10"
    echo "  ./aoc.sh run --day=6,7 --part=2"
    echo "  ./aoc.sh run --help"
    echo ""
}

# Function to check if Docker image exists
check_docker_image() {
    if ! docker image inspect "$IMAGE_NAME" &>/dev/null; then
        echo -e "\nFirst run detected! No $IMAGE_NAME docker image found, running docker build...\n"
        build_docker_image
        return 1
    fi
    return 0
}

# Function to check if vendor directory exists
check_vendor() {
    if [ ! -d "vendor" ]; then
        echo -e "\nFirst run detected! No vendor/ folder found, running composer update...\n"
        run_composer "update"
        return 1
    fi
    return 0
}

# Function to build Docker image
build_docker_image() {
    DOCKER_BUILDKIT=1 docker build --build-arg UID="$UID" --build-arg GID="$GID" \
        --tag="$IMAGE_NAME" \
        -f Dockerfile .
}

# Function to run Docker container
run_docker() {
    docker run -it --rm --init \
        --name "$IMAGE_NAME" \
        -u "$UID:$GID" \
        -v "$(pwd):/app" \
        -e PHP_IDE_CONFIG="serverName=$IMAGE_NAME" \
        -w /app \
        "$@"
}

# Function to run PHP script
run_php() {
    check_docker_image || return
    check_vendor || return
    run_docker "$IMAGE_NAME" php $PHP_TWEAKS run.php "$@"
}

# Function to run composer
run_composer() {
    check_docker_image || return
    run_docker "$IMAGE_NAME" composer --no-cache "$@"
}

# Function to run with xdebug
run_xdebug() {
    check_docker_image || return
    check_vendor || return
    run_docker -e XDEBUG_MODE=debug "$IMAGE_NAME" php -dmemory_limit=1G run.php "$@"
}

# Function to run xdebug profiler
run_xdebug_profile() {
    check_docker_image || return
    check_vendor || return
    run_docker -e XDEBUG_MODE=profile "$IMAGE_NAME" php -dxdebug.output_dir=/app -dmemory_limit=1G run.php "$@"
}

# Function to get input
get_input() {
    local latestDay
    if [[ "$(uname -s | tr A-Z a-z)" == "linux" ]]; then
        latestDay=$(find src/Days -maxdepth 1 -type f \( -name "Day[0-9][0-9].php" -o -name "Day[0-9].php" \) -printf '%f\n' | sort -Vr | head -1 | grep -o '[0-9]\+' || echo "1")
    else
        latestDay=$(find src/Days -maxdepth 1 -type f \( -name "Day[0-9][0-9].php" -o -name "Day[0-9].php" \) -print0 | xargs -0 stat -f '%N ' | sort -Vr | head -1 | grep -o '[0-9]\+' || echo "1")
    fi
    
    if [ -z "$AOC_COOKIE" ]; then
        echo -e "Missing AOC_COOKIE env\n\nPlease login to https://adventofcode.com/ and retrieve your session cookie."
        echo -e "Then set the environmental variable AOC_COOKIE. e.g. export AOC_COOKIE=53616c7465645f5f2b44c4d4742765e14...\n"
        return 1
    fi
    
    echo -e "Fetching latest input using day=$latestDay AOC_COOKIE=$AOC_COOKIE"
    curl -s --location --request GET "https://adventofcode.com/2022/day/$latestDay/input" --header "Cookie: session=$AOC_COOKIE" -o "./input/day$latestDay.txt" && echo "./input/day$latestDay.txt downloaded" || echo "error downloading"
}

# Function to create next day file
create_next_day() {
    echo "Creating next day's file..."
    next_day=$(ls src/Days | grep -oE 'Day[0-9]+' | sort -V | tail -n 1 | sed 's/Day//')
    next_day=$((next_day + 1))
    sed "s/DayX/Day$next_day/g" stub/DayX.php.stub > "src/Days/Day$next_day.php"
    echo "Created src/Days/Day$next_day.php"
    get_input
}

# Main command processing
case "$1" in
    run)
        shift
        run_php "$@"
        ;;
    build)
        build_docker_image
        ;;
    shell)
        check_docker_image || exit
        run_docker "$IMAGE_NAME" /bin/bash
        ;;
    composer)
        shift
        if [ $# -eq 0 ]; then
            run_composer "update"
        else
            run_composer "$@"
        fi
        ;;
    xdebug)
        shift
        run_xdebug "$@"
        ;;
    xdebug-profile)
        run_xdebug_profile
        ;;
    pint)
        check_docker_image || exit
        run_docker "$IMAGE_NAME" composer --no-cache run pint
        ;;
    phpstan)
        check_docker_image || exit
        run_docker "$IMAGE_NAME" composer run phpstan
        ;;
    get-input)
        get_input
        ;;
    next)
        create_next_day
        ;;
    help|--help|-h)
        show_help
        ;;
    *)
        show_help
        exit 1
        ;;
esac

exit 0