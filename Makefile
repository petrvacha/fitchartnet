.PHONY: help build up down restart logs logs-php logs-db shell composer composer-install npm db-shell db-import db-export clean ps

COMPOSE_FILE = docker/development/docker-compose.yml
COMPOSE_PROJECT = fitchartnet

help: ## Zobrazí nápovědu
	@echo "Dostupné příkazy:"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}'

build: ## Sestaví Docker images
	@echo "Building Docker images..."
	cd docker/development && docker compose -p $(COMPOSE_PROJECT) build

up: ## Spustí kontejnery
	@echo "Starting containers..."
	cd docker/development && docker compose -p $(COMPOSE_PROJECT) up -d

down: ## Zastaví kontejnery
	@echo "Stopping containers..."
	cd docker/development && docker compose -p $(COMPOSE_PROJECT) down

restart: ## Restartuje kontejnery
	@echo "Restarting containers..."
	cd docker/development && docker compose -p $(COMPOSE_PROJECT) restart

rebuild: ## Přesestaví images a restartuje kontejnery
	@echo "Rebuilding images and restarting containers..."
	cd docker/development && docker compose -p $(COMPOSE_PROJECT) up -d --build --force-recreate

logs: ## Zobrazí logy všech kontejnerů
	cd docker/development && docker compose -p $(COMPOSE_PROJECT) logs -f

logs-php: ## Zobrazí logy PHP kontejneru
	cd docker/development && docker compose -p $(COMPOSE_PROJECT) logs -f php

logs-db: ## Zobrazí logy databázového kontejneru
	cd docker/development && docker compose -p $(COMPOSE_PROJECT) logs -f database

shell: ## Vstoupí do PHP kontejneru
	docker exec -it $(COMPOSE_PROJECT) bash

composer: ## Spustí composer příkaz (použití: make composer CMD="install")
	docker exec -it $(COMPOSE_PROJECT) composer $(CMD)

composer-install: ## Nainstaluje Composer závislosti
	docker exec -it $(COMPOSE_PROJECT) composer install

npm: ## Spustí npm příkaz (použití: make npm CMD="install")
	docker exec -it $(COMPOSE_PROJECT) npm $(CMD)

db-shell: ## Vstoupí do MySQL shellu
	cd docker/development && docker compose -p $(COMPOSE_PROJECT) exec database mariadb -utest -ptest test

db-import: ## Importuje SQL dump (použití: make db-import FILE=sql/development.sql)
	@if [ ! -f "$(FILE)" ]; then \
		echo "Error: File $(FILE) not found"; \
		exit 1; \
	fi
	cd docker/development && docker compose -p $(COMPOSE_PROJECT) exec -T database mariadb -utest -ptest test < ../../$(FILE)

db-export: ## Exportuje databázi
	cd docker/development && docker compose -p $(COMPOSE_PROJECT) exec -T database mariadb-dump -utest -ptest test > ../../sql/export_$(shell date +%Y%m%d_%H%M%S).sql

clean: ## Zastaví a odstraní kontejnery, volumes a images
	@echo "Cleaning up..."
	cd docker/development && docker compose -p $(COMPOSE_PROJECT) down -v --rmi local

ps: ## Zobrazí stav kontejnerů
	cd docker/development && docker compose -p $(COMPOSE_PROJECT) ps

