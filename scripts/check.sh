#!/bin/sh
set -eu
APP_DIR=${APP_DIR:-app}
DC=${DC:-docker compose}
python3 scripts/check-demo.py
python3 scripts/vendor-assets.py --check
python3 scripts/check-artifacts.py --ref "${ARTIFACT_REF:-HEAD}"
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
# Console-Aufrufe oben laufen als root; unter Linux müsste PHP-FPM (www-data) sonst in
# root-eigene Cache-Verzeichnisse schreiben (Template-Studio-Inspektion fehlt dann).
$DC exec -T php sh -c 'chown -R www-data:www-data var assets files'
vp exec playwright test
