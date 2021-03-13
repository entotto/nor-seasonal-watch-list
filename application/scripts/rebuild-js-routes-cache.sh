#!/bin/bash

# Rebuild the application/public/js/fos_js_routes.json file, which
# caches exposed Symfony routes in a form usable by javascript functions
# in the browser.

TARGET='public/js/fos_js_routes.json'

# Determine the containing directory of this script
SOURCE="${BASH_SOURCE[0]}"
while [ -h "$SOURCE" ]; do # resolve $SOURCE until the file is no longer a symlink
  DIR="$( cd -P "$( dirname "$SOURCE" )" >/dev/null 2>&1 && pwd )"
  SOURCE="$(readlink "$SOURCE")"
  [[ $SOURCE != /* ]] && SOURCE="$DIR/$SOURCE" # if $SOURCE was a relative symlink, we need to resolve it relative to the path where the symlink file was located
done
DIR="$( cd -P "$( dirname "$SOURCE" )" >/dev/null 2>&1 && pwd )"

# Change to the 'application' directory
cd "$DIR/.." || exit

bin/console fos:js-routing:dump --target="$TARGET"

echo "The file '$TARGET' has been rebuilt."
