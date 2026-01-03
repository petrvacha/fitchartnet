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



