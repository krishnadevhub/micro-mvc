# MicroMVC - PHP MVC Framework

A lightweight MVC framework built with PHP 8.4 and Symfony components, containerised with Docker.

## Features

- **Routing** - YAML-based route configuration via Symfony Routing
- **Dependency Injection** - Autowired service container using Symfony DependencyInjection with compiled cache
- **Templating** - Twig templating engine with custom `asset()` and `path()`/`url()` functions
- **Error Handling** - Whoops pretty error pages in development, custom error pages in production
- **Logging** - Structured application logging via Monolog
- **Database Migrations** - Phinx migration support for schema versioning
- **Environment Configuration** - `.env`-based configuration via Symfony Dotenv

## Tech Stack

| Component   | Version        |
|-------------|----------------|
| PHP         | 8.4 (FPM)     |
| Nginx       | Latest         |
| MariaDB     | 10.6.7         |
| Composer    | Latest         |
| Node.js     | 18.x           |
| Yarn        | Latest         |

### Symfony Packages (v8.0)

- `symfony/routing` - URL matching and generation
- `symfony/http-foundation` - HTTP request/response abstraction
- `symfony/dependency-injection` - Service container with autowiring
- `symfony/dotenv` - Environment variable loader
- `symfony/yaml` - YAML file parser (routes, services config)
- `symfony/var-dumper` - Debug variable dumper (dev only)

### Other Packages

- `twig/twig` - Templating engine
- `filp/whoops` - Error handler with pretty stack traces
- `monolog/monolog` - PSR-3 logging
- `robmorgan/phinx` - Database migrations
- `phpstan/phpstan` - Static analysis (dev only)

## Project Structure

```
.
├── config/
│   ├── routes.yaml          # Route definitions
│   └── services.yaml        # DI service configuration
├── db/
│   ├── migrations/          # Phinx database migrations
│   └── seeds/               # Phinx database seeders
├── docker/
│   ├── nginx/               # Nginx Dockerfile and config
│   └── php/                 # PHP-FPM Dockerfile, php.ini, xdebug config
├── public/
│   ├── index.php            # Application entry point
│   └── asset/               # Static assets (images, CSS, JS)
├── src/
│   ├── Application.php      # Bootstrap, environment setup, path constants
│   ├── Controller/          # Route controllers extending AbstractController
│   ├── Core/
│   │   ├── AppContainer.php # Compiled DI container with caching
│   │   ├── Router.php       # Request-to-controller resolver
│   │   └── Twig/            # Custom Twig extensions (asset, routing)
│   └── Service/             # Application services (logger, business logic)
├── templates/               # Twig templates
├── composer.json
├── docker-compose.yml
└── phinx.json               # Migration configuration
```

## Setup

### Prerequisites

- Docker and Docker Compose

### Build and Run

Build and start the containers:

```bash
docker compose up -d --build
```

The `--build` flag is only required on first run or after Dockerfile changes. Subsequent starts:

```bash
docker compose up -d
```

### Configure Environment

Create a `.env` file from the distribution template:

```bash
cp .env.dist .env
```

Supported `APP_ENV` values: `dev`, `development`, `prod`, `production`

### Install Dependencies

```bash
docker exec -it micro-mvc-php-container composer install
```

### Run Database Migrations

```bash
docker exec -it micro-mvc-php-container vendor/bin/phinx migrate
```

### Access the Application

| Service     | URL                        |
|-------------|----------------------------|
| Application | http://localhost:10320     |
| Database    | localhost:3366 (MariaDB)   |

## Routes

| Name       | Path                           | Controller                                    |
|------------|--------------------------------|-----------------------------------------------|
| homepage   | `/`                            | `HomeController::indexAction`                  |
| product    | `/product/id/{id}/sid/{sid}`   | `ProductController::showAction`                |
| pdo-gen    | `/pdo-gen`                     | `PdoEntityGeneratorController::indexAction`    |

## Docker Services

| Container                     | Image            | Exposed Port |
|-------------------------------|------------------|--------------|
| micro-mvc-php-container       | php:8.4-fpm      | 9000 (internal) |
| micro-mvc-nginx-container     | nginx:latest     | 10320:80     |
| micro-mvc-db-container        | mariadb:10.6.7   | 3366:3306    |
