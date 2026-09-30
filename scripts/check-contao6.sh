#!/bin/sh
set -eu
DC6='docker compose -f compose.yaml -f compose.contao6.yaml'
restore_demo() {
    make down DC="$DC6"
    make up
}
# Restore the development stack even when a compatibility check fails.
trap restore_demo EXIT
make down
make up6
make check DC="$DC6" APP_DIR=app6
