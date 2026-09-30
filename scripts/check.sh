#!/bin/sh
set -eu
APP_DIR=${APP_DIR:-app}
DC=${DC:-docker compose}
python3 scripts/vendor-assets.py --check
$DC exec -T php vendor/bin/ecs check --config=/workspace/ecs.php --no-progress-bar
$DC exec -T php vendor/bin/twig-cs-fixer lint /workspace/packages
for package in packages/*/composer.json; do
    $DC exec -T php composer validate --strict "/workspace/$package"
    $DC exec -T php composer normalize --dry-run "/workspace/$package"
done
$DC exec -T php composer validate --strict
$DC exec -T php composer normalize --dry-run
$DC exec -T php php bin/console lint:twig /workspace/packages
$DC exec -T php php bin/console lint:yaml /workspace/packages /workspace/$APP_DIR/config /workspace/.github
$DC exec -T php php bin/console lint:container
# Paths in the analyzer and PHPUnit configuration follow the selected demo installation.
$DC exec -T php vendor/bin/phpstan analyse --configuration=/workspace/phpstan.neon.dist --no-progress --autoload-file=/workspace/$APP_DIR/vendor/autoload.php
$DC exec -T php vendor/bin/phpunit --configuration=/workspace/phpunit.xml.dist
vp check
vp exec playwright test
