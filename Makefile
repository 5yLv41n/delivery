#Setup automatically docker compose variables
include .env
-include .env.local

COMPOSE			:= docker-compose
COMPOSE_EXEC	:= $(COMPOSE) exec
PHP 			:= $(COMPOSE_EXEC) php
COMPOSER 		:= $(PHP) composer

### Common command targets ###
help: ## Show help
	@bin/generate-make-documentation "$(CURDIR)"

up: ## Start all containers
	@$(COMPOSE) pull --ignore-pull-failures --quiet &>/dev/null
	@$(COMPOSE) up -d --remove-orphans --build

down: ## Stop all containers
	@$(COMPOSE) down -v

start: ## Start project
	@$(MAKE) --silent up
	@$(MAKE) --silent install_dependencies

stop: ## Stop project
	@$(MAKE) --silent down

install_dependencies: ## Install project dependencies
	@$(COMPOSER) install

clean: down ## Stop all containers and clean project files
	@rm -rf vendor .git/hooks/pre-commit .git/hooks/commit-msg

### Symfony command targets ###


### Databases targets ###


### Test targets ###
test_phpunit_run_unit: ## Run unit tests
	@$(PHP) vendor/bin/simple-phpunit --testsuite unit

test_phpunit_run_integration: ## Run integration tests
	@$(PHP) vendor/bin/simple-phpunit --testsuite integration

test_phpunit_run: start_test ## Run PHPUnit test
	@$(PHP) vendor/bin/simple-phpunit

test.phpunit-coverage.run: reset_dbs_test
	@$(PHP) vendor/bin/simple-phpunit --coverage-html test-report/phpunit/coverage
	@$(PHP) vendor/bin/simple-phpunit --colors=never

tests: install_dependencies test_phpunit_run_unit test_phpunit_run_integration ## Run tests

### Static analysis targets ###
phpstan: ## Run phpstan
	@$(PHP) vendor/bin/phpstan analyse src

php-cs-fixer: ## Run php-cs-fixer
	@$(PHP) vendor/bin/php-cs-fixer fix

.PHONY: help clean tests install_dependencies start stop
