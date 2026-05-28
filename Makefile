.DEFAULT_GOAL := help

help: ## Show available commands
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-15s\033[0m %s\n", $$1, $$2}'

install: ## Full setup from scratch: env + containers + deps + db + cache
	cp .env.prod.example .env 2>/dev/null || copy .env.prod.example .env
	docker compose up -d --build
	docker compose exec php composer install --no-dev --optimize-autoloader --no-scripts
	docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction
	docker compose exec php php bin/console cache:clear
	docker compose exec php php bin/console cache:warmup

up: ## Start all containers
	docker compose up -d

down: ## Stop all containers
	docker compose down

build: ## Rebuild containers
	docker compose build --no-cache

bash: ## Open shell in PHP container
	docker compose exec php bash

composer-install: ## Install PHP dependencies
	docker compose exec php composer install

migrate: ## Run database migrations
	docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction

migrate-diff: ## Generate new migration
	docker compose exec php php bin/console doctrine:migrations:diff

fixtures: ## Load data fixtures
	docker compose exec php php bin/console doctrine:fixtures:load --no-interaction

cache-clear: ## Clear Symfony cache
	docker compose exec php php bin/console cache:clear

cache-warmup: ## Warm up Symfony cache
	docker compose exec php php bin/console cache:warmup

test: ## Run all tests
	docker compose exec php php bin/phpunit

test-unit: ## Run unit tests only
	docker compose exec php php bin/phpunit --testsuite Unit

test-integration: ## Run integration tests only
	docker compose exec php php bin/phpunit --testsuite Integration

test-functional: ## Run functional tests only
	docker compose exec php php bin/phpunit --testsuite Functional

cs: ## Check code style
	docker compose exec php vendor/bin/php-cs-fixer check --diff

cs-fix: ## Fix code style
	docker compose exec php vendor/bin/php-cs-fixer fix

stan: ## Run static analysis
	docker compose exec php vendor/bin/phpstan analyse

lint: ## Lint all PHP files
	docker compose exec php find src tests -name "*.php" -exec php -l {} \;

check: ## Run all pre-commit checks locally (same as CI)
	docker compose exec php find src tests -name "*.php" -exec php -l {} \;
	docker compose exec php vendor/bin/php-cs-fixer check --diff
	docker compose exec php vendor/bin/phpcs
	docker compose exec php vendor/bin/phpstan analyse

install-hooks: ## Install git pre-commit hook
	cp scripts/pre-commit .git/hooks/pre-commit
	chmod +x .git/hooks/pre-commit
	@echo "Pre-commit hook installed."

.PHONY: help install up down build bash composer-install migrate migrate-diff fixtures cache-clear cache-warmup test test-unit test-integration test-functional cs cs-fix stan lint check install-hooks
