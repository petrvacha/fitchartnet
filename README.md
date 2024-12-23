# Fitchart
 Fitchart is a vintage sports web application designed for creating sports challenges. After more than a decade of operation, it has been released as open-source software under the GNU GPLv3 license. You can access it at https://fitchart.net. [https://fitchart.net](https://fitchart.net).


## Installation
The development environment is pre-configured to run using Docker.

### Docker
To build the Docker containers, run:
```bash
./docker-build.sh
```


### Database
To set up the database, import the provided SQL dump, which includes tables, views, and a single user:
```
sql/development.sql
```


### Configuration
Copy the default configuration file to create a local configuration:
```bash
cp config/config.local.neon.dist config/config.local.neon
```



