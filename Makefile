COMMIT := $(shell git rev-parse --short=8 HEAD 2>/dev/null || echo "untagged")
BUILD_DIR := $(or $(BUILD_DIR),build)
BASENAME := $(shell basename $(PWD))
PACKAGE := crumb
ZIP_FILENAME := $(or $(ZIP_FILENAME),pkg_$(PACKAGE).zip)
ZIP_FILE := $(BUILD_DIR)/$(ZIP_FILENAME)
VENDOR_AUTOLOAD := vendor/autoload.php

ifeq ($(PROD)x, x)
	COMPOSER_ARGS := --prefer-dist --no-progress
else
	COMPOSER_ARGS := --no-dev
endif

help:  ## Print the help documentation
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

.PHONY: build
build:  ## Build the Joomla installable package zip
	@mkdir -p $(BUILD_DIR)
	@rm -f $(BUILD_DIR)/plg_content_crumb.zip $(BUILD_DIR)/mod_crumb.zip $(ZIP_FILE)
	@echo "▶ Zipping plugin…"
	cd packages/plugin && zip -qr ../../$(BUILD_DIR)/plg_content_crumb.zip . -x '*.DS_Store'
	@echo "▶ Zipping module…"
	cd packages/module && zip -qr ../../$(BUILD_DIR)/mod_crumb.zip . -x '*.DS_Store'
	@echo "▶ Assembling package…"
	@mkdir -p $(BUILD_DIR)/_pkg/packages $(BUILD_DIR)/_pkg/language/en-GB
	cp pkg_crumb.xml $(BUILD_DIR)/_pkg/
	cp $(BUILD_DIR)/plg_content_crumb.zip $(BUILD_DIR)/_pkg/packages/
	cp $(BUILD_DIR)/mod_crumb.zip $(BUILD_DIR)/_pkg/packages/
	cp language/en-GB/pkg_crumb.sys.ini $(BUILD_DIR)/_pkg/language/en-GB/
	echo $(COMMIT) > $(BUILD_DIR)/_pkg/build.txt
	cd $(BUILD_DIR)/_pkg && zip -qr ../$(ZIP_FILENAME) .
	@rm -rf $(BUILD_DIR)/_pkg
	@echo "✔ $(ZIP_FILE)"

.PHONY: clean
clean:  ## Remove build artifacts
	rm -rf $(BUILD_DIR)

$(VENDOR_AUTOLOAD):
	composer install $(COMPOSER_ARGS)

.PHONY: composer
composer: $(VENDOR_AUTOLOAD)  ## Run composer install

.PHONY: lint
lint: composer  ## Run PHP_CodeSniffer (PSR-12)
	vendor/bin/phpcs

.PHONY: fmt
fmt: composer  ## Auto-fix lint issues with phpcbf
	vendor/bin/phpcbf

.PHONY: test
test: composer  ## Run PHPUnit unit tests
	vendor/bin/phpunit --colors=always

.PHONY: dev
dev:  ## Start the local Joomla stack via docker compose
	docker compose up --build

.PHONY: down
down:  ## Stop the local Joomla stack
	docker compose down

.PHONY: nuke
nuke:  ## Stop the stack and wipe volumes (fresh DB next time)
	docker compose down -v

.PHONY: shell
shell:  ## Open a shell in the running joomla container
	docker compose exec joomla bash

.PHONY: install
install: build  ## Build and install the package into the running Joomla container
	docker compose cp $(ZIP_FILE) joomla:/tmp/$(ZIP_FILENAME)
	docker compose exec -T joomla php cli/joomla.php extension:install --path=/tmp/$(ZIP_FILENAME)

.PHONY: logs
logs:  ## Tail logs from the joomla container
	docker compose logs -f joomla
