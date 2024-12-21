#!/usr/bin/env bash

set -euo pipefail

if [ -z "$PHP_SESSION_COOKIE" ]; then
    echo 'Login cookie must be provided by PHP_SESSION_COOKIE. Log in to the site and look for a cookie named PHPSESSID in your browser, then export its value as environment variable.'
    exit 1
fi

script_dir=$( cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )

workers='1'
output="$script_dir/../test-results/screenshots"

while [[ $# -gt 0 ]]; do
    case $1 in
        --index-only)
            echo "will only write index.html"
            export SCREENSHOT_INDEX_ONLY=1
            workers='50%'
            shift
            ;;
        --output)
            output="$2"
            shift
            shift
            ;;
        *)
            echo "[warn] unknown flag $1"
            shift
            ;;
    esac
done

echo "output: $output"
export SCREENSHOTS_OUTPUT_DIR="$output"

cd "$script_dir/.."
npx playwright test --workers "$workers" --grep '@screenshot' --reporter './utils/screenshots-reporter.ts'
