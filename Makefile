.PHONY: help build up down restart logs logs-php logs-db shell composer composer-install npm db-shell db-import db-export deploy clean ps

COMPOSE_FILE = docker/development/docker-compose.yml
COMPOSE_PROJECT = fitchartnet

help: ## Show help
	@echo "Available commands:"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}'

build: ## Build Docker images
	@echo "Building Docker images..."
	cd docker/development && docker compose -p $(COMPOSE_PROJECT) build

up: ## Start containers
	@echo "Starting containers..."
	cd docker/development && docker compose -p $(COMPOSE_PROJECT) up -d

down: ## Stop containers
	@echo "Stopping containers..."
	cd docker/development && docker compose -p $(COMPOSE_PROJECT) down

restart: ## Restart containers
	@echo "Restarting containers..."
	cd docker/development && docker compose -p $(COMPOSE_PROJECT) restart

rebuild: ## Rebuild images and restart containers
	@echo "Rebuilding images and restarting containers..."
	cd docker/development && docker compose -p $(COMPOSE_PROJECT) up -d --build --force-recreate

logs: ## Show logs from all containers
	cd docker/development && docker compose -p $(COMPOSE_PROJECT) logs -f

logs-php: ## Show logs from PHP container
	cd docker/development && docker compose -p $(COMPOSE_PROJECT) logs -f php

logs-db: ## Show logs from database container
	cd docker/development && docker compose -p $(COMPOSE_PROJECT) logs -f database

shell: ## Enter PHP container
	docker exec -it $(COMPOSE_PROJECT) bash

composer: ## Run composer command (usage: make composer CMD="install")
	docker exec -it $(COMPOSE_PROJECT) composer $(CMD)

composer-install: ## Install Composer dependencies
	docker exec -it $(COMPOSE_PROJECT) composer install

npm: ## Run npm command (usage: make npm CMD="install")
	docker exec -it $(COMPOSE_PROJECT) npm $(CMD)

db-shell: ## Enter MySQL shell
	cd docker/development && docker compose -p $(COMPOSE_PROJECT) exec database mariadb -utest -ptest test

db-import: ## Import SQL dump (usage: make db-import FILE=sql/development.sql)
	@if [ ! -f "$(FILE)" ]; then \
		echo "Error: File $(FILE) not found"; \
		exit 1; \
	fi
	cd docker/development && docker compose -p $(COMPOSE_PROJECT) exec -T database mariadb -utest -ptest test < ../../$(FILE)

db-export: ## Export database
	cd docker/development && docker compose -p $(COMPOSE_PROJECT) exec -T database mariadb-dump -utest -ptest test > ../../sql/export_$(shell date +%Y%m%d_%H%M%S).sql

deploy: ## Deploy to production via FTP
	docker exec -it -w /var/www/html $(COMPOSE_PROJECT) php vendor/dg/ftp-deployment/deployment deploy.ini

clean: ## Stop and remove containers, volumes and images
	@echo "Cleaning up..."
	cd docker/development && docker compose -p $(COMPOSE_PROJECT) down -v --rmi local

ps: ## Show container status
	cd docker/development && docker compose -p $(COMPOSE_PROJECT) ps
