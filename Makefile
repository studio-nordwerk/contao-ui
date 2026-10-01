.PHONY: up down reset check e2e assets up6 check6 artifacts
DC ?= docker compose
APP_DIR ?= app
VERSION ?= 0.1.0-dev
ARTIFACT_DIR ?= dist/$(VERSION)

up:
	$(DC) up -d --build
	$(DC) exec -T php composer install --no-interaction --no-progress
	$(DC) exec -T php php bin/console contao:migrate --no-interaction --no-backup
	$(DC) exec -T php php /workspace/scripts/seed.php
	$(DC) exec -T php php /workspace/scripts/seed-teasers.php
	$(DC) exec -T php php bin/console contao:symlinks
	$(DC) exec -T php sh -c 'mkdir -p var/locks && chown -R www-data:www-data var assets files'
	vp install
	vp exec playwright install chromium

down:
	$(DC) down

reset:
	$(DC) down
	$(MAKE) up DC="$(DC)" APP_DIR="$(APP_DIR)"

check:
	APP_DIR="$(APP_DIR)" DC="$(DC)" ./scripts/check.sh

e2e:
	vp check
	vp exec playwright test

assets:
	python3 scripts/vendor-assets.py

up6:
	python3 scripts/prepare-contao6.py
	$(MAKE) up DC="docker compose -f compose.yaml -f compose.contao6.yaml" APP_DIR=app6

check6:
	./scripts/check-contao6.sh

artifacts:
	python3 scripts/check-artifacts.py
	python3 scripts/build-artifacts.py "$(VERSION)" "$(ARTIFACT_DIR)"
