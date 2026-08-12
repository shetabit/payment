# Runs the test suite and the quality checks of the package inside a container,
# so that no PHP installation is needed on the host.
#
#   make install      install the dependencies
#   make test         run the test suite
#   make ci           run every check the CI pipeline runs
#
# The PHP version can be changed per invocation:
#
#   make test PHP_VERSION=8.5
#
# A single Laravel version can be tested with:
#
#   make test-laravel LARAVEL=11
#
# Extra arguments can be handed over to docker with DOCKER_ARGS, which is handy
# when the host needs a custom certificate bundle or proxy settings:
#
#   make install DOCKER_ARGS="-e HTTPS_PROXY=$HTTPS_PROXY"

PHP_VERSION ?= 8.4
IMAGE ?= payment-dev:php$(PHP_VERSION)
DOCKER_ARGS ?=
LARAVEL ?=

UID := $(shell id -u)
GID := $(shell id -g)
TTY := $(shell [ -t 0 ] && echo --tty)

DOCKER_RUN = docker run --rm --interactive $(TTY) \
	--volume "$(CURDIR)":/app \
	--workdir /app \
	--user $(UID):$(GID) \
	$(DOCKER_ARGS) $(IMAGE)

.DEFAULT_GOAL := help

.PHONY: help build install update test test-laravel coverage check-style fix-style analyse rector ci shell clean

help: ## Show this help
	@grep -E '^[a-zA-Z-]+:.*?## .*$$' $(MAKEFILE_LIST) \
		| awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-14s\033[0m %s\n", $$1, $$2}'

build: ## Build the development image
	docker build --quiet --build-arg PHP_VERSION=$(PHP_VERSION) --tag $(IMAGE) . > /dev/null

install: build ## Install the composer dependencies
	$(DOCKER_RUN) composer install

update: build ## Update the composer dependencies to their latest version
	$(DOCKER_RUN) composer update

test: build vendor ## Run the test suite
	$(DOCKER_RUN) composer test

test-laravel: build ## Run the test suite against one Laravel version, e.g. LARAVEL=11
	@test -n "$(LARAVEL)" || { echo "Usage: make test-laravel LARAVEL=11"; exit 1; }
	$(DOCKER_RUN) composer update --with="laravel/framework:$(LARAVEL).*"
	$(DOCKER_RUN) composer test

coverage: build vendor ## Run the test suite and report code coverage
	$(DOCKER_RUN) composer test-coverage

check-style: build vendor ## Check the coding style
	$(DOCKER_RUN) composer check-style

fix-style: build vendor ## Fix the coding style where possible
	$(DOCKER_RUN) composer fix-style

analyse: build vendor ## Run static analysis
	$(DOCKER_RUN) composer analyse

rector: build vendor ## Show the refactorings rector would apply
	$(DOCKER_RUN) composer rector

ci: check-style analyse test ## Run every check the CI pipeline runs

shell: build vendor ## Open a shell inside the container
	$(DOCKER_RUN) bash

clean: ## Remove the dependencies, the tool caches and the image
	rm -rf vendor .phpunit.cache .phpunit.result.cache
	docker image rm --force $(IMAGE)

vendor: composer.json
	@$(MAKE) install
	@touch vendor
