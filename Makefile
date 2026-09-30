.PHONY: up down reset check e2e assets up6 check6
DC ?= docker compose
APP_DIR ?= app

up:
	$(DC) up -d --build
	$(DC) exec -T php composer install --no-interaction --no-progress
	$(DC) exec -T php php bin/console contao:migrate --no-interaction --no-backup
	$(DC) exec -T php php /workspace/scripts/seed.php
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
	$(MAKE) down
	$(MAKE) up6
	$(MAKE) check DC="docker compose -f compose.yaml -f compose.contao6.yaml" APP_DIR=app6
	$(MAKE) down DC="docker compose -f compose.yaml -f compose.contao6.yaml"
	$(MAKE) up
