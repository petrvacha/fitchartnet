# Fitchart
Fitchart is a vintage sports web application designed for creating sports challenges. After more than a decade of operation, it has been released as open-source software under the GNU GPLv3 license. You can access it at [https://fitchart.net](https://fitchart.net).


## Installation
The development environment is pre-configured to run using Docker.

### Prerequisites
- Docker and Docker Compose
- Make (optional, but recommended)

### Docker Setup

1. **Environment Setup (optional)**
   
   If you want to change default ports or credentials, copy and edit the environment variables file:
   ```bash
   cp docker/development/env.example docker/development/.env
   ```

2. **Starting Containers**
   
   For the first run, use:
   ```bash
   make build
   make up
   ```
   
   Or all at once:
   ```bash
   make rebuild
   ```

3. **Available Make Commands**
   
   ```bash
   make help          # Show all available commands
   make build         # Build Docker images
   make up            # Start containers
   make down          # Stop containers
   make restart       # Restart containers
   make rebuild       # Rebuild images and restart containers
   make logs          # Show logs from all containers
   make logs-php      # Show logs from PHP container
   make logs-db       # Show logs from database container
   make shell         # Enter PHP container
   make composer-install  # Install Composer dependencies
   make db-shell      # Enter MySQL shell
   make db-import FILE=sql/development.sql  # Import SQL dump
   make clean         # Stop and remove containers, volumes and images
   ```

### Database
To set up the database, import the provided SQL dump, which includes tables, views, and a single user:
```bash
make db-import FILE=sql/development.sql
```

### Configuration
Copy the default configuration file to create a local configuration:
```bash
cp app/config/config.local.neon.dist app/config/config.local.neon
```

### Access
- Web application: http://localhost:80
- phpMyAdmin: http://localhost:8081
- Database: localhost:3307 (user: test, password: test, database: test)

## Tests
- Install dev dependency if missing: `composer require --dev nette/tester`
- Optional env vars for the test DB (defaults work in Docker/PHP container and CI):
  - `TEST_DB_DSN` or granular `TEST_DB_HOST`/`TEST_DB_PORT`/`TEST_DB_NAME`
  - `TEST_DB_USER` (default `test`) - used for application operations
  - `TEST_DB_PASSWORD` (default `test`)
  - `TEST_DB_ROOT_USER` (default `root`) - used for DDL operations (DROP/CREATE DATABASE)
  - `TEST_DB_ROOT_PASSWORD` (default `root`)
- The suite loads `sql/development.sql` into the test database before each test case.
- Run tests from project root: `vendor/bin/tester -C tests`
- GitHub Actions workflow (`.github/workflows/tests.yml`) runs the suite automatically with a MySQL service.


