# MicroMVC Wiki

A comprehensive reference for the MicroMVC framework — a lightweight MVC application built with PHP 8.4 and Symfony components, containerised with Docker. It includes session-based authentication, Twig templating, Webpack asset bundling, and Phinx database migrations.

## Table of Contents

- [Getting Started](#getting-started)
- [Architecture Overview](#architecture-overview)
- [Configuration](#configuration)
- [Routing](#routing)
- [Controllers](#controllers)
- [Entities and Repositories](#entities-and-repositories)
- [Services](#services)
- [Twig Templating](#twig-templating)
- [Authentication](#authentication)
- [Database Migrations](#database-migrations)
- [Asset Bundling](#asset-bundling)
- [Docker Environment](#docker-environment)
- [Error Handling and Logging](#error-handling-and-logging)
- [Testing and Static Analysis](#testing-and-static-analysis)
- [Deployment](#deployment)
- [Contributing](#contributing)

---

## Getting Started

This section walks through setting up the MicroMVC project on a local development machine.

### Prerequisites

- **Docker** and **Docker Compose** (v3.9+)

Docker handles all runtime dependencies (PHP, Nginx, MariaDB, Node.js, Composer) so nothing else needs to be installed on the host machine.

### Quick Start

#### 1. Clone the Repository

```bash
git clone <repository-url>
cd <project-directory>
```

#### 2. Create the Environment File

Copy the distribution template to create a local `.env` file:

```bash
cp .env.dist .env
```

The `.env.dist` file ships with `APP_ENV=dev` which is the recommended setting for local development.

Supported `APP_ENV` values:

| Value        | Behaviour                                                    |
|--------------|--------------------------------------------------------------|
| `dev`        | Debug mode on, Whoops error pages, full error reporting      |
| `development`| Alias for `dev`                                              |
| `prod`       | Debug mode off, custom error pages, errors suppressed        |
| `production` | Alias for `prod`                                             |

#### 3. Build and Start the Containers

```bash
docker compose up -d --build
```

The `--build` flag is only required on first run or after Dockerfile changes. Subsequent starts:

```bash
docker compose up -d
```

#### 4. Install Dependencies

```bash
docker exec -it micro-mvc-php-container composer install
docker exec -it micro-mvc-php-container npm install
```

#### 5. Build Frontend Assets

**Development build** (with source maps):

```bash
docker exec -it micro-mvc-php-container npm run dev
```

**Watch mode** (auto-rebuild on file changes):

```bash
docker exec -it micro-mvc-php-container npm run watch
```

**Production build** (minified, no source maps):

```bash
docker exec -it micro-mvc-php-container npm run build
```

#### 6. Run Database Migrations

```bash
docker exec -it micro-mvc-php-container vendor/bin/phinx migrate
```

#### 7. Access the Application

| Service     | URL                      |
|-------------|--------------------------|
| Application | http://localhost:10320   |
| Database    | localhost:3366 (MariaDB) |

### Stopping the Environment

```bash
docker compose down
```

To also remove the persistent database volume:

```bash
docker compose down -v
```

### Troubleshooting

| Issue                            | Solution                                                         |
|----------------------------------|------------------------------------------------------------------|
| Port 10320 already in use        | Change the port mapping in `docker-compose.yml` under `nginx`    |
| Port 3366 already in use         | Change the port mapping in `docker-compose.yml` under `database` |
| Composer install fails           | Ensure the PHP container is running: `docker compose ps`         |
| Assets not loading               | Run `npm run dev` inside the PHP container                       |
| Database connection refused      | Wait a few seconds for MariaDB to initialise, then retry         |

---

## Architecture Overview

MicroMVC follows the Model-View-Controller (MVC) pattern using PHP 8.4 with Symfony components for routing, dependency injection, and HTTP abstraction.

### Tech Stack

| Component   | Version     | Purpose                               |
|-------------|-------------|---------------------------------------|
| PHP         | 8.4 (FPM)  | Application runtime                   |
| Nginx       | Latest      | Web server and reverse proxy          |
| MariaDB     | 10.6.7      | Relational database                   |
| Composer    | Latest      | PHP dependency management             |
| Node.js     | 22.x        | Asset build tooling                   |
| Webpack     | 5.x         | CSS/JS/font/image bundling            |

#### Symfony Packages (v8.0)

| Package                          | Role                                 |
|----------------------------------|--------------------------------------|
| `symfony/routing`                | URL matching and generation          |
| `symfony/http-foundation`        | HTTP request/response abstraction    |
| `symfony/dependency-injection`   | Service container with autowiring    |
| `symfony/dotenv`                 | Environment variable loader          |
| `symfony/yaml`                   | YAML file parser                     |
| `symfony/var-dumper`             | Debug variable dumper (dev only)     |

#### Other Packages

| Package                          | Role                                      |
|----------------------------------|-------------------------------------------|
| `twig/twig`                      | Templating engine                         |
| `filp/whoops`                    | Pretty error pages (dev only)             |
| `monolog/monolog`                | PSR-3 structured logging                  |
| `robmorgan/phinx`                | Database migrations                       |
| `kdevhubin/pdoentitygenerator`   | PDO entity/repository code generation     |
| `phpstan/phpstan`                | Static analysis (dev only)                |

### Request Lifecycle

```
Browser Request
      │
      ▼
┌──────────┐
│  Nginx   │  Receives HTTP request on port 80 (mapped to host 10320)
└────┬─────┘
     │ FastCGI pass to php:9000
     ▼
┌──────────────────┐
│  public/index.php │  Application entry point
└────────┬─────────┘
         │
         ▼
┌────────────────────┐
│  Application::init │  Loads .env, validates environment, registers Whoops (dev)
└────────┬───────────┘
         │
         ▼
┌────────────────┐
│  AppContainer  │  Builds/loads compiled DI container from services.yaml
└────────┬───────┘
         │
         ▼
┌──────────────────┐
│  Router::resolve │  Matches URI against routes.yaml, resolves controller
└────────┬─────────┘
         │
         ▼
┌─────────────────────────────────┐
│  Controller::action             │  Business logic, calls services/repositories
│  └─ AbstractController::render  │  Renders Twig template with data
└─────────────────────────────────┘
         │
         ▼
    HTML Response
```

### Project Structure

```
.
├── config/                     # Application configuration
│   ├── pdoentitygenerator.yaml # Database connection and code generation config
│   ├── routes.yaml             # Route definitions
│   └── services.yaml           # DI service configuration
├── db/
│   └── migrations/             # Phinx database migrations
├── docker/
│   ├── nginx/                  # Nginx Dockerfile and virtual host config
│   └── php/                    # PHP-FPM Dockerfile, php.ini, Xdebug config
├── assets/                     # Source assets (pre-build)
│   ├── css/                    # CSS source files (Bootstrap, custom styles)
│   ├── fontawesome/            # FontAwesome icon library
│   ├── images/                 # Source images (favicon, logo)
│   └── js/                     # JS source files (Bootstrap, jQuery)
├── public/
│   ├── build/                  # Webpack output (generated, gitignored)
│   └── index.php               # Application entry point
├── src/
│   ├── Application.php         # Bootstrap, environment setup, path constants
│   ├── Controller/             # MVC controllers
│   ├── Core/                   # Framework internals (DI container, router, Twig extensions)
│   ├── Entity/                 # Data model classes
│   ├── Factory/                # Object creation factories (PDO)
│   ├── Repository/             # Database access layer
│   └── Service/                # Application services (logging, business logic)
├── templates/                  # Twig templates
├── docs/                       # Documentation
├── logs/                       # Application log files
├── composer.json               # PHP dependencies
├── docker-compose.yml          # Docker service definitions
├── package.json                # Node.js dependencies and build scripts
├── webpack.config.js           # Webpack bundling configuration
└── phinx.json                  # Migration configuration
```

### Autoloading

The project uses PSR-4 autoloading. The `App\` namespace maps to the `src/` directory:

```json
{
    "autoload": {
        "psr-4": {
            "App\\": "src/"
        }
    }
}
```

Test classes use the `App\Tests\` namespace mapped to `tests/`.

### Path Constants

`Application::loadPathConstants()` defines the following global constants used throughout the framework:

| Constant        | Value                            | Purpose                          |
|-----------------|----------------------------------|----------------------------------|
| `APP_ROOT`      | Project root directory           | Base path for all other paths    |
| `PUBLIC_PATH`   | `public`                         | Public-facing directory name     |
| `CONFIG_PATH`   | `{APP_ROOT}/config`              | Configuration files directory    |
| `ASSET_PATH`    | `{APP_ROOT}/public/build`        | Compiled asset output directory  |
| `TEMPLATE_PATH` | `{APP_ROOT}/templates`           | Twig template directory          |
| `CACHE_PATH`    | `{APP_ROOT}/var/cache`           | Compiled container and route cache |

---

## Configuration

MicroMVC uses three main configuration files, all located in the `config/` directory, plus a `.env` file at the project root for environment-specific settings.

### Environment Variables (`.env`)

The `.env` file is loaded by `symfony/dotenv` during application bootstrap. It is gitignored — use `.env.dist` as a template.

| Variable  | Required | Values                                   | Default |
|-----------|----------|------------------------------------------|---------|
| `APP_ENV` | Yes      | `dev`, `development`, `prod`, `production` | `dev`   |

#### Environment Behaviour

| Feature              | `dev` / `development`           | `prod` / `production`            |
|----------------------|---------------------------------|----------------------------------|
| Error display        | Full PHP errors shown           | Errors suppressed                |
| Error handler        | Whoops pretty pages             | Custom error page template       |
| Debug mode           | Enabled                         | Disabled                         |
| DI container cache   | Rebuilt when config changes     | Cached until manually cleared    |
| Route cache          | Rebuilt when config changes     | Cached until manually cleared    |
| Twig cache           | Debug mode enabled              | Compiled and cached              |

### Service Container (`config/services.yaml`)

The DI container is configured using Symfony's `services.yaml` format:

```yaml
services:
    _defaults:
        autowire: true
        autoconfigure: true

    App\:
        resource: '../src/'
        exclude:
            - '../src/Core'
            - '../src/Exception'
            - '../src/Utils'
            - '../src/Application.php'
        public: true

    PDO:
        class: PDO
        factory: [ 'App\Factory\PdoFactory', 'create' ]
```

#### Key Points

- **Autowiring** is enabled by default — constructor dependencies are resolved automatically from the container.
- **Autoconfigure** is enabled — services are automatically tagged (e.g. as commands, event subscribers).
- All classes under `src/` are registered as services, except `Core/`, `Exception/`, `Utils/`, and `Application.php`.
- The `PDO` service is created via `PdoFactory::create()`, which reads database credentials from `pdoentitygenerator.yaml`.
- Services are marked `public: true` so they can be fetched directly from the container by class name.

#### Excluded Directories

| Path                   | Reason                                               |
|------------------------|------------------------------------------------------|
| `src/Core`             | Framework internals, not application services        |
| `src/Exception`        | Exception classes, no dependencies to inject         |
| `src/Utils`            | Utility/helper classes, stateless                    |
| `src/Application.php`  | Bootstrap class, runs before the container is built  |

### Database Configuration (`config/pdoentitygenerator.yaml`)

This file configures both the database connection and the entity/repository code generator:

```yaml
database:
    host: database        # Docker service name
    port: 3306
    dbname: micromvc
    username: root
    password: root
    driver: mysql

output:
    entity_namespace: App\Entity
    repository_namespace: App\Repository
    entity_directory: src/Entity
    repository_directory: src/Repository
```

#### Database Section

| Key        | Description                          | Default     |
|------------|--------------------------------------|-------------|
| `host`     | Database hostname                    | `127.0.0.1` |
| `port`     | Database port                        | `3306`      |
| `dbname`   | Database name (required)             | —           |
| `username` | Database user                        | `root`      |
| `password` | Database password                    | (empty)     |
| `driver`   | PDO driver                           | `mysql`     |

#### Output Section

Controls where the `pdoentitygenerator` Composer plugin generates entity and repository classes.

| Key                    | Description                    |
|------------------------|--------------------------------|
| `entity_namespace`     | PHP namespace for entities     |
| `repository_namespace` | PHP namespace for repositories |
| `entity_directory`     | Output directory for entities  |
| `repository_directory` | Output directory for repositories |

### Phinx Migration Configuration (`phinx.json`)

Database migration environments are configured in `phinx.json` at the project root:

| Environment   | Host       | Database    | Port |
|---------------|------------|-------------|------|
| `development` | `database` | `micromvc`  | 3306 |
| `production`  | `localhost`| `test`      | 3306 |
| `testing`     | `localhost`| `testing_db`| 3366 |

The default environment is `development`, which uses the Docker service name `database` as the host.

---

## Routing

MicroMVC uses Symfony's Routing component to match incoming HTTP requests to controller actions. Routes are defined in YAML and support parameterised URLs with validation constraints.

### Route Definitions

All routes are defined in `config/routes.yaml`:

```yaml
homepage:
    path: /
    defaults: { _controller: 'App\Controller\HomeController::indexAction' }

product:
    path: /product/id/{id}/sid/{sid}
    defaults: { _controller: 'App\Controller\ProductController::showAction' }
    requirements: { id: '[0-9]+', sid: '[0-9]+' }

login:
    path: /login
    defaults: { _controller: 'App\Controller\AuthController::loginAction' }

register:
    path: /register
    defaults: { _controller: 'App\Controller\AuthController::registerAction' }

logout:
    path: /logout
    defaults: { _controller: 'App\Controller\AuthController::logoutAction' }

profile:
    path: /profile
    defaults: { _controller: 'App\Controller\ProfileController::indexAction' }
```

### Route Table

| Name       | Path                          | Controller                          | Parameters        |
|------------|-------------------------------|-------------------------------------|--------------------|
| `homepage` | `/`                           | `HomeController::indexAction`       | —                  |
| `product`  | `/product/id/{id}/sid/{sid}` | `ProductController::showAction`     | `id`, `sid` (int)  |
| `login`    | `/login`                      | `AuthController::loginAction`       | —                  |
| `register` | `/register`                   | `AuthController::registerAction`    | —                  |
| `logout`   | `/logout`                     | `AuthController::logoutAction`      | —                  |
| `profile`  | `/profile`                    | `ProfileController::indexAction`    | —                  |

### How Routing Works

The `Router` class (`src/Core/Router.php`) handles the full request-to-controller dispatch:

1. **Create request context** — a `RequestContext` is populated from the current HTTP request via `Request::createFromGlobals()`.
2. **Load routes** — routes are loaded from `routes.yaml` using Symfony's `YamlFileLoader`. Route definitions are cached in `var/cache/` for performance.
3. **Match the URI** — the router matches the request path against defined routes. On a match, it returns the `_controller` value and any route parameters.
4. **Resolve the controller** — the `_controller` string is split into class and method. The class is fetched from the DI container, ensuring all dependencies are injected.
5. **Dispatch** — the controller method is called with the matched route parameters.
6. **Error handling** — `ResourceNotFoundException` returns a 404 response; `MethodNotAllowedException` returns a 405 response. Both are logged via `AppLogger`.

### Route Parameters

Route parameters are defined using `{placeholder}` syntax. Constraints can be applied via the `requirements` key:

```yaml
product:
    path: /product/id/{id}/sid/{sid}
    defaults: { _controller: 'App\Controller\ProductController::showAction' }
    requirements: { id: '[0-9]+', sid: '[0-9]+' }
```

Parameters are passed as arguments to the controller method. The parameter names must match the method signature:

```php
public function showAction(int $id, int $sid): void
```

Internal route attributes (prefixed with `_`) are filtered out before dispatch.

### Adding a New Route

1. **Define the route** in `config/routes.yaml`:

    ```yaml
    dashboard:
        path: /dashboard
        defaults: { _controller: 'App\Controller\DashboardController::indexAction' }
    ```

2. **Create the controller** class (see [Controllers](#controllers)).

3. **Clear the route cache** — in development mode the cache auto-refreshes. In production, delete the `var/cache/` directory.

### URL Generation in Templates

Two Twig functions are available for generating URLs from route names:

#### `path()` — Relative Path

```twig
<a href="{{ path('homepage') }}">Home</a>
<a href="{{ path('product', {'id': 1, 'sid': 13}) }}">Product</a>
```

#### `url()` — Absolute URL

```twig
<a href="{{ url('homepage') }}">Home</a>
```

Both functions are provided by the `RoutingExtension` Twig extension. See [Twig Templating](#twig-templating) for more details.

---

## Controllers

Controllers handle incoming requests, delegate to services/repositories, and render responses via Twig templates. All controllers extend `AbstractController`.

### Controller Directory

All controllers reside in `src/Controller/`:

| Controller               | Purpose                           |
|--------------------------|-----------------------------------|
| `AbstractController`     | Base class — Twig rendering       |
| `AuthController`         | Login, registration, logout       |
| `HomeController`         | Homepage                          |
| `ProductController`      | Product detail page               |
| `ProfileController`      | Authenticated user profile        |
| `ErrorPageController`    | Generic error page                |
| `PageNotFoundController` | 404 not found page                |

### AbstractController

`AbstractController` (`src/Controller/AbstractController.php`) provides the `render()` method used by all controllers.

#### `render(string $template, array $args = []): void`

Renders a Twig template and outputs the HTML directly.

**Behaviour:**

- Initialises the Twig environment once (static singleton) with the `templates/` directory as the loader root.
- Registers two custom Twig extensions:
  - `AssetExtension` — provides the `asset()` function
  - `RoutingExtension` — provides the `path()` and `url()` functions
- Starts a PHP session if one is not already active.
- Injects `session_user` into every template context (the currently authenticated user, or `null`).
- Uses compiled Twig cache stored in `var/cache/`.

#### Usage

```php
$this->render('product.html.twig', [
    'product' => $productData,
]);
```

### AuthController

Handles all authentication flows. See [Authentication](#authentication) for detailed behaviour.

| Method            | Route      | Description                        |
|-------------------|------------|------------------------------------|
| `loginAction()`   | `/login`   | Login form display and processing  |
| `registerAction()`| `/register`| Registration form and processing   |
| `logoutAction()`  | `/logout`  | Session teardown and redirect      |

**Dependencies** (injected via constructor):

- `UserRepository` — for user lookup and creation

### HomeController

Renders the homepage. No dependencies.

```php
public function indexAction(): void
{
    $this->render('home.html.twig');
}
```

### ProductController

Renders product detail pages with injected services.

**Dependencies** (injected via constructor):

- `TestInvoiceService` — processes invoices with tax calculation

```php
public function showAction(int $id, int $sid): void
```

Route parameters `id` and `sid` are passed directly from the router.

### ProfileController

Displays the authenticated user's profile. Redirects unauthenticated users to `/login`.

```php
public function indexAction(): void
```

Checks `$_SESSION['user']` to determine authentication state.

### Error Controllers

| Controller               | Template                   | Purpose            |
|--------------------------|----------------------------|--------------------|
| `ErrorPageController`    | `error.html.twig`          | Generic errors     |
| `PageNotFoundController` | `page_not_found.html.twig` | 404 responses      |

These are standalone controllers — they do not depend on any services.

### Creating a New Controller

1. **Create the class** in `src/Controller/`:

    ```php
    <?php

    declare(strict_types=1);

    namespace App\Controller;

    class DashboardController extends AbstractController
    {
        public function indexAction(): void
        {
            $this->render('dashboard.html.twig', [
                'title' => 'Dashboard',
            ]);
        }
    }
    ```

2. **Add a route** in `config/routes.yaml`:

    ```yaml
    dashboard:
        path: /dashboard
        defaults: { _controller: 'App\Controller\DashboardController::indexAction' }
    ```

3. **Create the template** in `templates/dashboard.html.twig`:

    ```twig
    {% extends 'base.html.twig' %}

    {% block title %}{{ title }}{% endblock %}

    {% block body %}
    <div class="container mt-5">
        <h1>{{ title }}</h1>
    </div>
    {% endblock %}
    ```

4. The controller is automatically registered as a service via the `App\` resource declaration in `services.yaml` — no manual service registration is needed.

### Dependency Injection in Controllers

Controllers support constructor injection. Declare dependencies as constructor parameters and the DI container resolves them automatically:

```php
public function __construct(
    private readonly UserRepository $userRepository,
) {
}
```

The container must have a matching service registered. All classes under `src/` (except excluded directories) are auto-registered.

---

## Entities and Repositories

MicroMVC uses a manual entity/repository pattern built on PDO. Entities are plain PHP objects representing database rows. Repositories encapsulate all SQL queries and handle hydration.

### Entities

Entities reside in `src/Entity/`. Each entity maps to a database table and uses private properties with getter/setter methods. Setters return `$this` for fluent chaining.

#### User Entity

**Class:** `App\Entity\User`
**Table:** `user`

| Property     | Type                 | Column        | Notes                        |
|--------------|----------------------|---------------|------------------------------|
| `id`         | `?string`            | `id`          | Auto-increment primary key   |
| `firstName`  | `?string`            | `first_name`  |                              |
| `surname`    | `?string`            | `surname`     |                              |
| `email`      | `?string`            | `email`       | Unique index                 |
| `password`   | `?string`            | `password`    | Bcrypt hash                  |
| `createdAt`  | `?DateTimeImmutable`  | `created_at`  | Set on insert                |
| `updatedAt`  | `?DateTimeImmutable`  | `updated_at`  | Set on insert and update     |

#### Employee Entity

**Class:** `App\Entity\Employee`
**Table:** `employee`

| Property    | Type      | Column      |
|-------------|-----------|-------------|
| `id`        | `?string` | `id`        |
| `surname`   | `?string` | `surname`   |
| `firstname` | `?string` | `firstname` |
| `salary`    | `?string` | `salary`    |

### Repositories

Repositories reside in `src/Repository/`. Each repository receives a `PDO` instance via constructor injection and provides CRUD methods.

#### Common Methods

Both repositories implement a consistent interface:

| Method                        | Description                          |
|-------------------------------|--------------------------------------|
| `find(string $id)`            | Find a single record by primary key  |
| `insert(Entity $entity)`      | Insert a new record                  |
| `update(Entity $entity)`      | Update an existing record            |
| `delete(string $id)`          | Delete a record by primary key       |
| `static create()`             | Factory method using `PdoFactory`    |

#### UserRepository

**Class:** `App\Repository\UserRepository`

Additional methods:

| Method                          | Description                       |
|---------------------------------|-----------------------------------|
| `findByEmail(string $email)`    | Look up a user by email address   |

**Key behaviours:**

- `insert()` automatically sets `createdAt` and `updatedAt` to the current timestamp.
- `update()` automatically refreshes `updatedAt`.
- The `id` property is set via reflection after insert (since it has no public setter).
- All bind values use explicit PDO parameter types (`PDO::PARAM_STR` or `PDO::PARAM_NULL`).

#### EmployeeRepository

**Class:** `App\Repository\EmployeeRepository`

Additional methods:

| Method       | Description                |
|--------------|----------------------------|
| `findAll()`  | Retrieve all employees     |

#### Entity Hydration

Both repositories use a private `hydrateEntity()` method to convert associative arrays (from `PDO::FETCH_ASSOC`) into entity objects. The `id` property is set via `ReflectionProperty` since it is read-only (no public setter).

```php
private function hydrateEntity(array $row): User
{
    $entity = new User();

    $reflection = new \ReflectionProperty($entity, 'id');
    $reflection->setValue($entity, (string) $row['id']);

    $entity->setFirstName($row['first_name'] !== null ? (string) $row['first_name'] : null);
    // ... remaining properties
}
```

### PDO Factory

**Class:** `App\Factory\PdoFactory`
**File:** `src/Factory/PdoFactory.php`

Creates configured PDO database connections using a singleton pattern.

#### Configuration Source

Reads credentials from `config/pdoentitygenerator.yaml` under the `database` key.

#### Connection Defaults

| Key        | Default     |
|------------|-------------|
| `host`     | `127.0.0.1` |
| `port`     | `3306`      |
| `dbname`   | (required)  |
| `username` | `root`      |
| `password` | (empty)     |
| `driver`   | `mysql`     |

#### PDO Options

The factory configures PDO with the following attributes:

| Attribute                    | Value              | Purpose                          |
|------------------------------|--------------------|----------------------------------|
| `PDO::ATTR_ERRMODE`         | `ERRMODE_EXCEPTION`| Throw exceptions on errors       |
| `PDO::ATTR_DEFAULT_FETCH_MODE` | `FETCH_ASSOC`   | Return associative arrays        |
| `PDO::ATTR_EMULATE_PREPARES`| `false`            | Use native prepared statements   |

#### Usage

**Via DI container** (recommended — configured in `services.yaml`):

```php
public function __construct(private readonly \PDO $pdo) {}
```

**Standalone:**

```php
$pdo = PdoFactory::create();
$repository = new EmployeeRepository($pdo);
```

**Reset connection** (e.g. for testing):

```php
PdoFactory::reset();
```

### Code Generation

Entity and repository classes can be auto-generated from the database schema using the `kdevhubin/pdoentitygenerator` Composer plugin. Configuration for the generator is in `config/pdoentitygenerator.yaml` under the `output` key.

### Creating a New Entity and Repository

1. **Create the entity** in `src/Entity/`:

    ```php
    <?php

    declare(strict_types=1);

    namespace App\Entity;

    class Order
    {
        private ?string $id = null;
        private ?string $total = null;

        public function getId(): ?string { return $this->id; }
        public function getTotal(): ?string { return $this->total; }

        public function setTotal(?string $total): self
        {
            $this->total = $total;
            return $this;
        }
    }
    ```

2. **Create the repository** in `src/Repository/` following the same pattern as `UserRepository` or `EmployeeRepository`.

3. **Create a migration** for the database table (see [Database Migrations](#database-migrations)).

4. Both classes are automatically registered as services — no manual configuration is needed.

---

## Services

Services contain reusable business logic and are managed by Symfony's dependency injection container with autowiring.

### Service Directory

All services reside in `src/Service/`:

| Service                | Purpose                                    |
|------------------------|--------------------------------------------|
| `AppLogger`            | Application logging via Monolog            |
| `TestInvoiceService`   | Invoice processing with tax and email      |
| `TestSalesTaxService`  | Sales tax calculation                      |
| `TestEmailService`     | Email dispatch simulation                  |

### AppLogger

**Class:** `App\Service\AppLogger`

Wraps Monolog to provide structured application logging.

| Parameter    | Default               | Description                     |
|--------------|-----------------------|---------------------------------|
| `$loggerName`| `'app'`               | Monolog channel name            |
| `$errorLevel`| `Logger::WARNING`     | Minimum level to log            |

Logs are written to `{LOG_PATH}/app.log` via a `StreamHandler`.

#### Usage

```php
$logger = new AppLogger();
$logger->getLogger()->error('Something went wrong');
$logger->getLogger()->warning('Unusual behaviour detected');
```

The `AppLogger` is also available as an autowired service:

```php
public function __construct(
    private readonly AppLogger $logger,
) {}
```

### TestInvoiceService

**Class:** `App\Service\TestInvoiceService`

Demonstrates service composition via dependency injection. Processes an invoice by:

1. Calculating sales tax via `TestSalesTaxService`
2. Sending a receipt email via `TestEmailService`
3. Logging any errors via `AppLogger`

#### Dependencies

| Dependency             | Role                     |
|------------------------|--------------------------|
| `TestSalesTaxService`  | Tax calculation          |
| `TestEmailService`     | Email dispatch           |
| `AppLogger`            | Error logging            |

#### Method: `process(array $customers, float $amount): bool`

- Calculates tax on the given `$amount` for the provided `$customers`.
- On success, sends a `'receipt'` email and returns the send result.
- On failure, logs the exception and re-throws it.

### TestSalesTaxService

**Class:** `App\Service\TestSalesTaxService`

Calculates sales tax at a fixed rate.

| Constant              | Value | Description          |
|-----------------------|-------|----------------------|
| `TAX_RATE_PERCENTAGE` | 6.5   | Tax rate percentage  |
| `PERCENTAGE_DIVISOR`  | 100   | Divisor for rate     |

#### Method: `calculate(float $amount, array $customers): float`

Returns `$amount * 6.5 / 100`.

### TestEmailService

**Class:** `App\Service\TestEmailService`

Simulates email dispatch. Always returns `true`.

#### Method: `send(array $to, string $template): bool`

Accepts recipient addresses and a template identifier. Currently a stub for testing the DI wiring.

### Dependency Injection

#### How It Works

Services are automatically registered by the `App\` resource declaration in `config/services.yaml`:

```yaml
services:
    _defaults:
        autowire: true
        autoconfigure: true

    App\:
        resource: '../src/'
        exclude:
            - '../src/Core'
            - '../src/Exception'
            - '../src/Utils'
            - '../src/Application.php'
        public: true
```

**Autowiring** resolves constructor parameters by type-hint. If a service requires `TestSalesTaxService`, the container automatically injects the singleton instance.

#### Container Compilation

The `AppContainer` class compiles the service definitions into a cached PHP file (`var/cache/container.php`) for performance. In development mode, the cache is refreshed when configuration files change. In production, the cache persists until manually cleared.

### Creating a New Service

1. **Create the class** in `src/Service/`:

    ```php
    <?php

    declare(strict_types=1);

    namespace App\Service;

    class NotificationService
    {
        public function __construct(
            private readonly AppLogger $logger,
        ) {
        }

        public function notify(string $message): void
        {
            $this->logger->getLogger()->info($message);
        }
    }
    ```

2. The service is automatically registered — inject it into any controller or other service via constructor type-hinting:

    ```php
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {}
    ```

3. **Clear the container cache** — in development the cache auto-refreshes. In production, delete `var/cache/container.php`.

---

## Twig Templating

MicroMVC uses [Twig](https://twig.symfony.com/) as its templating engine. Templates are stored in the `templates/` directory and rendered by controllers via `AbstractController::render()`.

### Template Directory

| Template                  | Purpose                                |
|---------------------------|----------------------------------------|
| `base.html.twig`          | Base layout — navbar, footer, assets   |
| `home.html.twig`          | Homepage content                       |
| `login.html.twig`         | Login form                             |
| `register.html.twig`      | Registration form                      |
| `profile.html.twig`       | Authenticated user profile             |
| `product.html.twig`       | Product detail page                    |
| `error.html.twig`         | Generic error page                     |
| `page_not_found.html.twig`| 404 not found page                     |

### Base Layout

`base.html.twig` defines the HTML skeleton used by all pages:

```twig
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" type="text/css" href="{{ asset('build/app.css') }}">
    <title>{% block title %}{% endblock %}</title>
    {% block stylesheets %}{% endblock %}
    {% block javascripts %}{% endblock %}
</head>
<body>
    <nav><!-- Conditional navbar --></nav>
    {% block body %}{% endblock %}
    <footer><!-- Copyright --></footer>
    <script src="{{ asset('build/app.js') }}"></script>
</body>
</html>
```

#### Available Blocks

| Block          | Purpose                                  |
|----------------|------------------------------------------|
| `title`        | Page title in `<title>` tag              |
| `stylesheets`  | Additional page-specific CSS             |
| `javascripts`  | Additional page-specific JavaScript      |
| `body`         | Main page content                        |

#### Conditional Navbar

The navbar dynamically adjusts based on authentication state. The `session_user` variable is injected into every template by `AbstractController::render()`:

```twig
{% if session_user is defined and session_user %}
    {# Show Profile and Logout links #}
{% else %}
    {# Show Login link #}
{% endif %}
```

### Custom Twig Extensions

Two custom extensions are registered automatically by `AbstractController`:

#### AssetExtension

**Class:** `App\Core\Twig\AssetExtension`
**File:** `src/Core/Twig/AssetExtension.php`

Provides the `asset()` function for generating public URLs to static assets.

```twig
<link rel="stylesheet" href="{{ asset('build/app.css') }}">
<img src="{{ asset('build/images/logo.png') }}">
```

The function prepends the application's base URL (protocol + host) to the given path.

#### RoutingExtension

**Class:** `App\Core\Twig\RoutingExtension`
**File:** `src/Core/Twig/RoutingExtension.php`

Provides `path()` and `url()` functions for generating URLs from named routes.

##### `path(name, parameters, relative)`

Generates a relative path:

```twig
<a href="{{ path('homepage') }}">Home</a>
<a href="{{ path('product', {'id': 5, 'sid': 10}) }}">Product</a>
```

##### `url(name, parameters, schemeRelative)`

Generates an absolute URL:

```twig
<a href="{{ url('homepage') }}">Home</a>
```

Both functions include compile-time safety analysis — URLs with zero or one query parameter are automatically marked as HTML-safe to avoid unnecessary escaping.

### Twig Configuration

| Setting  | Development          | Production              |
|----------|----------------------|-------------------------|
| Cache    | `var/cache/`         | `var/cache/`            |
| Debug    | Enabled              | Disabled                |

### Creating a New Template

1. **Create the template file** in `templates/`:

    ```twig
    {% extends 'base.html.twig' %}

    {% block title %}My Page{% endblock %}

    {% block body %}
    <div class="container mt-5">
        <h1>Hello, {{ name }}</h1>
    </div>
    {% endblock %}
    ```

2. **Render it from a controller:**

    ```php
    $this->render('my_page.html.twig', [
        'name' => 'World',
    ]);
    ```

### Template Variables

Every template automatically receives:

| Variable       | Type            | Description                              |
|----------------|-----------------|------------------------------------------|
| `session_user` | `array\|null`   | Authenticated user data, or `null`       |

The `session_user` array contains `id`, `first_name`, `surname`, and `email` when a user is logged in.

---

## Authentication

MicroMVC includes a session-based authentication system with registration, login, profile viewing, and logout. All authentication logic resides in `AuthController`.

### Overview

| Feature        | Route       | Method              | Description                          |
|----------------|-------------|----------------------|--------------------------------------|
| Registration   | `/register` | `registerAction()`   | Create a new user account            |
| Login          | `/login`    | `loginAction()`      | Authenticate and start a session     |
| Profile        | `/profile`  | `indexAction()`      | View authenticated user details      |
| Logout         | `/logout`   | `logoutAction()`     | End the session and clear cookies    |

### Registration Flow

**Route:** `GET /register` (form display) and `POST /register` (form submission)

1. If the user is already authenticated (`$_SESSION['user']` exists), redirect to `/profile`.
2. On `POST`, validate the submitted fields:

    | Field        | Validation Rules                                |
    |--------------|-------------------------------------------------|
    | `first_name` | Required, non-empty                             |
    | `surname`    | Required, non-empty                             |
    | `email`      | Required, valid email format (`FILTER_VALIDATE_EMAIL`) |
    | `password`   | Required, minimum 8 characters                  |

3. Check for duplicate email addresses via `UserRepository::findByEmail()`.
4. Hash the password using `password_hash()` with `PASSWORD_DEFAULT` (bcrypt).
5. Insert the new user via `UserRepository::insert()`.
6. Regenerate the session ID to prevent session fixation.
7. Store user data in `$_SESSION['user']` and redirect to `/profile`.

### Login Flow

**Route:** `GET /login` (form display) and `POST /login` (form submission)

1. If the user is already authenticated, redirect to `/profile`.
2. On `POST`, validate that both `email` and `password` are provided.
3. Look up the user by email via `UserRepository::findByEmail()`.
4. Verify the password using `password_verify()` against the stored bcrypt hash.
5. On success:
   - Regenerate the session ID (`session_regenerate_id(true)`) to prevent session fixation.
   - Store user data in `$_SESSION['user']`.
   - Redirect to `/profile`.
6. On failure, display `'Invalid email or password.'`.

### Session Data

On successful login or registration, the following data is stored in `$_SESSION['user']`:

```php
$_SESSION['user'] = [
    'id'         => $user->getId(),
    'first_name' => $user->getFirstName(),
    'surname'    => $user->getSurname(),
    'email'      => $user->getEmail(),
];
```

### Profile Page

**Route:** `GET /profile`
**Controller:** `ProfileController::indexAction()`

- Checks for `$_SESSION['user']`. If absent, redirects to `/login`.
- Renders `profile.html.twig` with the session user data.

### Logout Flow

**Route:** `GET /logout`
**Controller:** `AuthController::logoutAction()`

1. Clear all session data: `$_SESSION = []`.
2. If session cookies are enabled, expire the session cookie by setting it to a past timestamp.
3. Destroy the session: `session_destroy()`.
4. Redirect to `/login`.

### Security Measures

| Measure                  | Implementation                                                |
|--------------------------|---------------------------------------------------------------|
| Password hashing         | `password_hash()` with `PASSWORD_DEFAULT` (bcrypt)            |
| Password verification    | `password_verify()` — constant-time comparison                |
| Session fixation         | `session_regenerate_id(true)` on login and registration       |
| Cookie cleanup on logout | Explicit cookie expiry with original cookie parameters        |
| Input validation         | Server-side validation for all form fields                    |
| Duplicate prevention     | Unique email check before registration                        |

### Navbar Integration

The base layout template (`base.html.twig`) dynamically adjusts navigation links based on `session_user`:

- **Authenticated:** Profile, Logout
- **Guest:** Login

---

## Database Migrations

MicroMVC uses [Phinx](https://phinx.org/) for database schema versioning. Migrations allow the database schema to be evolved incrementally and consistently across environments.

### Configuration

Migration settings are defined in `phinx.json` at the project root:

```json
{
    "paths": {
        "migrations": "db/migrations",
        "seeds": "db/seeds"
    },
    "environments": {
        "default_migration_table": "phinxlog",
        "default_environment": "development",
        "development": {
            "adapter": "mysql",
            "host": "database",
            "name": "micromvc",
            "user": "root",
            "pass": "root",
            "port": 3306,
            "charset": "utf8"
        }
    }
}
```

#### Environments

| Environment   | Host        | Database     | Port | Use Case                 |
|---------------|-------------|--------------|------|--------------------------|
| `development` | `database`  | `micromvc`   | 3306 | Docker local development |
| `production`  | `localhost` | `test`       | 3306 | Production deployment    |
| `testing`     | `localhost` | `testing_db` | 3366 | Automated tests          |

The default environment is `development`, which uses the Docker Compose service name `database` as the host.

### Migration Directory

Migrations are stored in `db/migrations/`. Each migration file is named with a timestamp prefix:

```
db/migrations/20260530174700_create_user_table.php
```

### Existing Migrations

#### CreateUserTable

**File:** `20260530174700_create_user_table.php`

Creates the `user` table:

| Column       | Type                        | Constraints                                |
|--------------|-----------------------------|--------------------------------------------|
| `id`         | `INT(11)`                   | `AUTO_INCREMENT`, `PRIMARY KEY`            |
| `first_name` | `VARCHAR(255)`              | Nullable                                   |
| `surname`    | `VARCHAR(255)`              | Nullable                                   |
| `email`      | `VARCHAR(180)`              | `NOT NULL`, `UNIQUE KEY`                   |
| `password`   | `VARCHAR(255)`              | `NOT NULL`                                 |
| `created_at` | `DATETIME`                  | `NOT NULL`                                 |
| `updated_at` | `TIMESTAMP`                 | `NOT NULL`, auto-updates on change         |

Uses `utf8mb4_unicode_ci` collation and InnoDB engine.

### Running Migrations

All commands must be executed inside the PHP Docker container:

#### Apply All Pending Migrations

```bash
docker exec -it micro-mvc-php-container vendor/bin/phinx migrate
```

#### Apply to a Specific Environment

```bash
docker exec -it micro-mvc-php-container vendor/bin/phinx migrate -e production
```

#### Check Migration Status

```bash
docker exec -it micro-mvc-php-container vendor/bin/phinx status
```

#### Rollback the Last Migration

```bash
docker exec -it micro-mvc-php-container vendor/bin/phinx rollback
```

### Creating a New Migration

#### Generate a Migration File

```bash
docker exec -it micro-mvc-php-container vendor/bin/phinx create CreateOrdersTable
```

This creates a new file in `db/migrations/` with a timestamp prefix.

#### Write the Migration

Use the `change()` method for reversible migrations:

```php
<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateOrdersTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('orders');
        $table->addColumn('user_id', 'integer')
              ->addColumn('total', 'decimal', ['precision' => 10, 'scale' => 2])
              ->addColumn('status', 'string', ['limit' => 50, 'default' => 'pending'])
              ->addColumn('created_at', 'datetime')
              ->addForeignKey('user_id', 'user', 'id', ['delete' => 'CASCADE'])
              ->addIndex(['user_id'])
              ->create();
    }
}
```

Alternatively, use raw SQL via `$this->execute()` as demonstrated in the existing `CreateUserTable` migration.

### Migration Tracking

Phinx tracks which migrations have been applied using a `phinxlog` table in the target database. This table is created automatically on the first migration run.

### Seeds

Seed files for populating test data can be placed in `db/seeds/`. Run seeds with:

```bash
docker exec -it micro-mvc-php-container vendor/bin/phinx seed:run
```

---

## Asset Bundling

MicroMVC uses Webpack 5 to bundle CSS, JavaScript, fonts, and images into production-ready assets.

### Source Assets

Source files are in the `assets/` directory:

```
assets/
├── css/
│   ├── bootstrap/    # Bootstrap CSS source
│   ├── common/       # Shared custom styles
│   └── app.css       # Main CSS entry point
├── fontawesome/      # FontAwesome icon library
├── images/           # Source images (favicon, logo)
└── js/
    ├── bootstrap/    # Bootstrap JS source
    ├── jquery/       # jQuery library
    └── app.js        # Main JS entry point
```

### Build Output

Webpack outputs compiled assets to `public/build/` (gitignored):

| File              | Description                                   |
|-------------------|-----------------------------------------------|
| `app.css`         | Bundled Bootstrap + FontAwesome + custom CSS   |
| `app.js`          | Bundled Bootstrap JS (includes Popper.js)      |
| `fonts/`          | FontAwesome webfont files                      |
| `images/`         | Copied image assets (favicon, logo)            |
| `manifest.json`   | Asset manifest for programmatic lookups        |

### Webpack Configuration

The Webpack config is in `webpack.config.js` at the project root.

#### Entry Point

```javascript
entry: {
    app: './assets/js/app.js'
}
```

The main entry point is `assets/js/app.js`, which imports all required CSS and JavaScript.

#### Output

```javascript
output: {
    filename: '[name].js',
    path: path.resolve(__dirname, 'public/build'),
    publicPath: '/build/'
}
```

#### Loaders

| Rule                        | Loader                    | Purpose                        |
|-----------------------------|---------------------------|--------------------------------|
| `.css`                      | `css-loader` + `MiniCssExtractPlugin` | Extract CSS to separate file |
| `.woff`, `.woff2`, `.eot`, `.ttf`, `.otf`, `.svg` | `asset/resource` | Copy font files to `fonts/` |
| `.png`, `.jpg`, `.jpeg`, `.gif` | `asset/resource`       | Copy images to `images/`       |

#### Plugins

| Plugin                  | Purpose                                             |
|-------------------------|-----------------------------------------------------|
| `CleanWebpackPlugin`    | Clears the `public/build/` directory before each build |
| `MiniCssExtractPlugin`  | Extracts CSS into a separate `app.css` file         |
| `CopyWebpackPlugin`     | Copies `assets/images/` to `public/build/images/`   |
| `WebpackManifestPlugin` | Generates `manifest.json` mapping logical names to output paths |

#### Source Maps

- **Development:** Source maps enabled (`source-map`)
- **Production:** Source maps disabled

### Build Commands

All commands run inside the PHP Docker container (which includes Node.js 22.x):

| Command                                                | Mode        | Description                     |
|--------------------------------------------------------|-------------|---------------------------------|
| `docker exec -it micro-mvc-php-container npm run dev`  | Development | Build with source maps          |
| `docker exec -it micro-mvc-php-container npm run watch`| Development | Auto-rebuild on file changes    |
| `docker exec -it micro-mvc-php-container npm run build`| Production  | Minified build, no source maps  |

### Node.js Dependencies

Defined in `package.json`:

| Package                   | Version  | Purpose                       |
|---------------------------|----------|-------------------------------|
| `webpack`                 | ^5.97.1  | Module bundler                |
| `webpack-cli`             | ^6.0.1   | Webpack CLI                   |
| `css-loader`              | ^7.1.2   | CSS import handling           |
| `mini-css-extract-plugin` | ^2.9.2   | Extract CSS to files          |
| `clean-webpack-plugin`    | ^4.0.0   | Clean output directory        |
| `copy-webpack-plugin`     | ^14.0.0  | Copy static files             |
| `webpack-manifest-plugin` | ^5.0.0   | Generate asset manifest       |

### Using Assets in Templates

Assets are referenced in Twig templates using the `asset()` function:

```twig
<link rel="stylesheet" href="{{ asset('build/app.css') }}">
<script src="{{ asset('build/app.js') }}"></script>
<img src="{{ asset('build/images/logo.png') }}">
```

The `asset()` function prepends the application's base URL to produce a fully qualified path.

### Adding New Assets

#### Adding a CSS File

1. Create the CSS file in `assets/css/`.
2. Import it in `assets/js/app.js` (or the main CSS file):

    ```javascript
    import '../css/my-styles.css';
    ```

3. Rebuild: `npm run dev`.

#### Adding a JavaScript File

1. Create the JS file in `assets/js/`.
2. Import it in `assets/js/app.js`:

    ```javascript
    import './my-module.js';
    ```

3. Rebuild: `npm run dev`.

#### Adding Images

Place image files in `assets/images/`. They are automatically copied to `public/build/images/` by `CopyWebpackPlugin`.

---

## Docker Environment

MicroMVC runs in a multi-container Docker environment using Docker Compose. Three services work together: PHP-FPM for application processing, Nginx as the web server, and MariaDB for the database.

### Container Overview

| Container                   | Image            | Exposed Port     | Purpose                    |
|-----------------------------|------------------|------------------|----------------------------|
| `micro-mvc-php-container`   | php:8.4-fpm      | 9000 (internal)  | PHP application runtime    |
| `micro-mvc-nginx-container` | nginx:latest     | 10320 → 80       | Web server / reverse proxy |
| `micro-mvc-db-container`    | mariadb:10.6.7   | 3366 → 3306      | Database server            |

All containers are connected via the `micro-mvc-net` Docker network.

### Docker Compose Configuration

```yaml
version: "3.9"

services:
  php:
    build:
      context: ./docker
      dockerfile: ./php/Dockerfile
    container_name: micro-mvc-php-container
    volumes:
      - ./:/var/www/html/:cached

  nginx:
    build:
      context: ./docker
      dockerfile: ./nginx/Dockerfile
    container_name: micro-mvc-nginx-container
    volumes:
      - ./:/var/www/html/:cached
    ports:
      - "10320:80"
    depends_on:
      - database
      - php

  database:
    image: mariadb:10.6.7
    container_name: micro-mvc-db-container
    ports:
      - "3366:3306"
    environment:
      - MYSQL_DATABASE=test
      - MYSQL_ROOT_PASSWORD=root
    volumes:
      - persistent:/var/lib/mysql
```

#### Volume Mounts

- The project root is mounted into both `php` and `nginx` containers at `/var/www/html/` with the `:cached` flag for improved performance on macOS.
- The `persistent` named volume preserves MariaDB data across container restarts.

### PHP Container

#### Dockerfile (`docker/php/Dockerfile`)

Built from `php:8.4-fpm` with the following additions:

| Component        | Details                                    |
|------------------|--------------------------------------------|
| System packages  | `git`, `zip`, `curl`, `wget`, `nano`, `g++`, `libicu-dev`, `libzip-dev` |
| PHP extensions   | `intl`, `pdo`, `pdo_mysql`, `zip`, `apcu`, `xdebug` |
| Composer         | Latest version, copied from official image |
| Node.js          | v22.x (via NodeSource)                     |
| Yarn             | Latest, installed globally via npm         |
| Timezone         | `Asia/Kuala_Lumpur` (configurable via `TIMEZONE` build arg) |

#### PHP Configuration

Custom `php.ini` settings are applied via `docker/php/php.ini`.

#### Xdebug

Xdebug is installed and configured via `docker/php/xdebug.ini`. Composer commands use an alias that disables Xdebug to avoid performance overhead:

```bash
alias composer="XDEBUG_MODE=off composer"
```

#### User Configuration

The `www-data` user is configured with UID/GID 1000 (matching the typical host user) to avoid file permission issues with the mounted volume.

### Nginx Container

#### Dockerfile (`docker/nginx/Dockerfile`)

Built from `nginx:latest`:

- Copies `docker/nginx/default.conf` as the virtual host configuration.
- Creates an upstream configuration pointing to the PHP-FPM container: `upstream php-upstream { server php:9000; }`.
- Sets `www-data` UID to 1000 for permission consistency.

#### Virtual Host Configuration (`docker/nginx/default.conf`)

| Setting                | Value                                    |
|------------------------|------------------------------------------|
| Listen port            | 80                                       |
| Server name            | `localhost`                              |
| Document root          | `/var/www/html/public`                   |
| Client max body size   | 200M                                     |
| FastCGI read timeout   | 600 seconds                              |
| Error log              | `/var/log/nginx/error.log`               |
| Access log             | `/var/log/nginx/access.log`              |

#### Request Handling

1. Static files are served directly by Nginx.
2. All other requests are rewritten to `index.php` via `try_files $uri $uri/ /index.php$is_args$args`.
3. PHP files are processed via FastCGI pass to the PHP container on port 9000.
4. Direct access to `.php` files (other than through the front controller) returns 404.

### MariaDB Container

Uses the official `mariadb:10.6.7` image with:

| Setting               | Value    |
|------------------------|----------|
| Default database       | `test`   |
| Root password          | `root`   |
| External port          | 3366     |
| Internal port          | 3306     |
| Data persistence       | Named volume `persistent` |

#### Connecting from the Host

```bash
mysql -h 127.0.0.1 -P 3366 -u root -proot
```

#### Connecting from Other Containers

Use the Docker service name `database` as the host and port `3306`.

### Common Docker Commands

| Command                                         | Description                    |
|-------------------------------------------------|--------------------------------|
| `docker compose up -d --build`                  | Build and start all containers |
| `docker compose up -d`                          | Start containers (no rebuild)  |
| `docker compose down`                           | Stop and remove containers     |
| `docker compose down -v`                        | Stop, remove containers and volumes |
| `docker compose ps`                             | List running containers        |
| `docker compose logs -f php`                    | Follow PHP container logs      |
| `docker exec -it micro-mvc-php-container bash`  | Shell into the PHP container   |
| `docker exec -it micro-mvc-db-container bash`   | Shell into the DB container    |

---

## Error Handling and Logging

MicroMVC uses different error handling strategies depending on the environment, with structured logging via Monolog.

### Error Handling

#### Development Mode (`dev` / `development`)

In development, the application enables full PHP error reporting and registers [Whoops](https://github.com/filp/whoops) for rich, interactive error pages:

```php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$whoops = new Run();
$whoops->pushHandler(new PrettyPageHandler());
$whoops->register();
```

Whoops provides:
- Stack trace with source code context
- Request and server information
- Environment variables
- Interactive code inspection

#### Production Mode (`prod` / `production`)

In production, errors are suppressed and handled by custom error page controllers:

| Controller               | Template                   | Use Case            |
|--------------------------|----------------------------|---------------------|
| `ErrorPageController`    | `error.html.twig`          | Unhandled exceptions |
| `PageNotFoundController` | `page_not_found.html.twig` | 404 not found       |

The entry point (`public/index.php`) catches top-level exceptions:

```php
try {
    Application::init();
} catch (Exception $ex) {
    (new ErrorPageController())->errorAction();
}
```

#### Router-Level Error Handling

The `Router::resolve()` method handles two specific HTTP error conditions:

| Exception                    | HTTP Status | Response             |
|------------------------------|-------------|----------------------|
| `ResourceNotFoundException`  | 404         | `'Not Found'`        |
| `MethodNotAllowedException`  | 405         | `'Method Not Allowed'` |

Both exceptions are logged before the error response is sent.

### Logging

#### AppLogger Service

**Class:** `App\Service\AppLogger`
**File:** `src/Service/AppLogger.php`

Wraps Monolog to provide a preconfigured logger instance.

| Setting      | Value                      |
|--------------|----------------------------|
| Channel name | `'app'` (default)          |
| Minimum level| `Logger::WARNING` (default)|
| Output       | `{LOG_PATH}/app.log`       |
| Handler      | `StreamHandler`            |

#### Usage

```php
// Via DI injection
public function __construct(private readonly AppLogger $logger) {}

$this->logger->getLogger()->error('Payment processing failed');
$this->logger->getLogger()->warning('Slow query detected');
$this->logger->getLogger()->info('User logged in');
```

#### Log Levels

Monolog supports the standard PSR-3 log levels:

| Level       | Value | Use Case                                        |
|-------------|-------|-------------------------------------------------|
| `DEBUG`     | 100   | Detailed diagnostic information                  |
| `INFO`      | 200   | General operational events                       |
| `NOTICE`    | 250   | Noteworthy but non-error events                  |
| `WARNING`   | 300   | Exceptional occurrences that are not errors       |
| `ERROR`     | 400   | Runtime errors that need attention                |
| `CRITICAL`  | 500   | Critical conditions                               |
| `ALERT`     | 550   | Immediate action required                         |
| `EMERGENCY` | 600   | System is unusable                                |

The default minimum level is `WARNING`, meaning `DEBUG`, `INFO`, and `NOTICE` messages are filtered out unless a custom level is specified.

#### Customising the Logger

```php
use Monolog\Logger;

// Log everything including debug messages
$debugLogger = new AppLogger('debug-channel', Logger::DEBUG);

// Only log errors and above
$errorLogger = new AppLogger('error-channel', Logger::ERROR);
```

#### Log Output

Logs are written to the `logs/` directory at the project root. The log directory is included in the Docker volume mount, making logs accessible from the host machine.

---

## Testing and Static Analysis

MicroMVC includes PHPStan for static analysis and has a test namespace configured for future test suites.

### Static Analysis with PHPStan

[PHPStan](https://phpstan.org/) is included as a dev dependency for static type analysis.

#### Installation

PHPStan is included in `composer.json` under `require-dev`:

```json
{
    "require-dev": {
        "phpstan/phpstan": "^1.7"
    }
}
```

#### Running PHPStan

```bash
docker exec -it micro-mvc-php-container vendor/bin/phpstan analyse src/
```

To specify a rule level (0 = loosest, 9 = strictest):

```bash
docker exec -it micro-mvc-php-container vendor/bin/phpstan analyse src/ --level 5
```

#### Configuration

PHPStan can be configured by creating a `phpstan.neon` file at the project root:

```neon
parameters:
    level: 5
    paths:
        - src
    excludePaths:
        - src/Core
```

### Test Setup

The project has a test autoload namespace configured in `composer.json`:

```json
{
    "autoload-dev": {
        "psr-4": {
            "App\\Tests\\": "tests/"
        }
    }
}
```

Test classes should be placed in the `tests/` directory using the `App\Tests` namespace.

#### Phinx Testing Environment

A dedicated testing database environment is configured in `phinx.json`:

| Setting    | Value        |
|------------|--------------|
| Host       | `localhost`  |
| Database   | `testing_db` |
| Port       | `3366`       |
| User       | `root`       |
| Password   | (empty)      |

Run migrations against the test environment:

```bash
docker exec -it micro-mvc-php-container vendor/bin/phinx migrate -e testing
```

### Debug Tools

#### Symfony VarDumper

The `symfony/var-dumper` package is included as a dev dependency. Use `dump()` and `dd()` for interactive debugging:

```php
dump($variable);    // Dump and continue
dd($variable);      // Dump and die
```

These functions produce formatted, colour-coded output in the browser (web context) or terminal (CLI context).

---

## Deployment

This section covers preparing and deploying the MicroMVC application to production.

### Production Build Checklist

1. **Set the environment** — ensure `.env` contains `APP_ENV=prod` or `APP_ENV=production`.
2. **Install production dependencies** — skip dev packages:

    ```bash
    composer install --no-dev --optimize-autoloader
    npm install --production
    ```

3. **Build production assets** — minified, no source maps:

    ```bash
    npm run build
    ```

4. **Run database migrations:**

    ```bash
    vendor/bin/phinx migrate -e production
    ```

5. **Clear caches** — remove the compiled container and route caches:

    ```bash
    rm -rf var/cache/*
    ```

6. **Verify file permissions** — the web server user must have write access to `var/cache/` and `logs/`.

### Environment Configuration

#### Production `.env`

```
APP_ENV=prod
```

#### Production Database

Update `config/pdoentitygenerator.yaml` with production database credentials:

```yaml
database:
    host: <production-db-host>
    port: 3306
    dbname: <production-db-name>
    username: <production-db-user>
    password: <production-db-password>
    driver: mysql
```

#### Production Phinx Configuration

Update the `production` environment in `phinx.json`:

```json
{
    "production": {
        "adapter": "mysql",
        "host": "<production-db-host>",
        "name": "<production-db-name>",
        "user": "<production-db-user>",
        "pass": "<production-db-password>",
        "port": 3306,
        "charset": "utf8"
    }
}
```

### Production Behaviour Differences

| Feature            | Development                      | Production                    |
|--------------------|----------------------------------|-------------------------------|
| Error pages        | Whoops interactive debugger      | Custom error templates        |
| Error reporting    | All PHP errors displayed         | Errors suppressed             |
| Debug mode         | Enabled                          | Disabled                      |
| DI container cache | Refreshed on config change       | Persistent until cleared      |
| Route cache        | Refreshed on config change       | Persistent until cleared      |
| Twig debug         | Enabled                          | Disabled                      |
| Asset source maps  | Included                         | Excluded                      |

### Docker Production Considerations

For production Docker deployments, consider:

- Remove Xdebug from the PHP container (or set `XDEBUG_MODE=off`).
- Use a production-tuned `php.ini` (disable `display_errors`, set appropriate `memory_limit`).
- Set strong database passwords (not the default `root`).
- Use Docker secrets or environment variables for sensitive configuration.
- Enable HTTPS via a reverse proxy or load balancer in front of the Nginx container.
- Configure proper log rotation for application and web server logs.

### Web Server Requirements

If deploying without Docker:

| Requirement       | Version / Detail                    |
|-------------------|-------------------------------------|
| PHP               | 8.4+ with FPM                      |
| Extensions        | `pdo`, `pdo_mysql`, `intl`, `zip`  |
| Web server        | Nginx or Apache with URL rewriting  |
| Database          | MySQL 5.7+ or MariaDB 10.6+        |
| Node.js           | 22.x (for asset building only)      |

#### Nginx Configuration

The key Nginx requirement is rewriting all requests to `public/index.php`:

```nginx
location / {
    try_files $uri $uri/ /index.php$is_args$args;
}
```

The document root must point to the `public/` directory.

---

## Contributing

Guidelines for contributing to the MicroMVC project.

### Code Standards

#### PHP

- **PHP version:** 8.4+ with `declare(strict_types=1)` in every file.
- **Namespace:** `App\` mapped to `src/` via PSR-4 autoloading.
- **Coding style:** Follow PSR-12 coding standards.
- **Type declarations:** Use typed properties, parameter types, and return types throughout.
- **Readonly properties:** Use `private readonly` for constructor-injected dependencies.
- **Fluent setters:** Entity setters return `$this` for method chaining.

#### JavaScript / CSS

- Source files go in `assets/`, not `public/build/`.
- Always rebuild assets after making changes: `npm run dev`.
- The `public/build/` directory is gitignored — never commit built assets.

#### Templates

- Templates use Twig syntax and reside in `templates/`.
- Extend `base.html.twig` for consistent layout.
- Use `path()` and `url()` for generating URLs, not hardcoded paths.
- Use `asset()` for referencing static assets.

### Project Structure Conventions

| Type        | Location                | Namespace                  |
|-------------|-------------------------|----------------------------|
| Controllers | `src/Controller/`       | `App\Controller`           |
| Entities    | `src/Entity/`           | `App\Entity`               |
| Repositories| `src/Repository/`       | `App\Repository`           |
| Services    | `src/Service/`          | `App\Service`              |
| Factories   | `src/Factory/`          | `App\Factory`              |
| Core/Framework | `src/Core/`          | `App\Core`                 |
| Templates   | `templates/`            | —                          |
| Migrations  | `db/migrations/`        | —                          |
| Assets      | `assets/`               | —                          |
| Configuration | `config/`             | —                          |

### Branching Strategy

- Create feature branches from the appropriate base branch.
- Use descriptive branch names, preferably prefixed with a ticket ID if applicable (e.g. `2226587-update-trs-statement`).
- Keep commits focused — one logical change per commit.
- Include ticket IDs in commit messages when applicable (e.g. `#2226587 - Update TRS statement date`).

### Pull Request Workflow

1. Push your feature branch to the remote.
2. Create a pull request targeting the appropriate base branch.
3. Provide a clear title and description of the changes.
4. Ensure all CI checks pass before requesting review.
5. Do not merge your own pull requests — wait for reviewer approval.

### Development Workflow

1. **Start the environment:**

    ```bash
    docker compose up -d
    ```

2. **Make code changes** — PHP changes take effect immediately (FPM reloads automatically).

3. **Rebuild assets** if modifying CSS/JS:

    ```bash
    docker exec -it micro-mvc-php-container npm run dev
    ```

    Or use watch mode for automatic rebuilds:

    ```bash
    docker exec -it micro-mvc-php-container npm run watch
    ```

4. **Run static analysis** before committing:

    ```bash
    docker exec -it micro-mvc-php-container vendor/bin/phpstan analyse src/
    ```

5. **Clear caches** if you change configuration files (routes, services):

    ```bash
    rm -rf var/cache/*
    ```

### Adding a New Feature

Typical steps for adding a new feature:

1. **Create a migration** if a new database table is needed.
2. **Create entity and repository** classes in `src/Entity/` and `src/Repository/`.
3. **Create a service** in `src/Service/` if business logic is needed.
4. **Create a controller** in `src/Controller/` extending `AbstractController`.
5. **Add a route** in `config/routes.yaml`.
6. **Create a template** in `templates/` extending `base.html.twig`.
7. **Run PHPStan** to verify type safety.
8. **Commit and push** your changes.

### Files to Avoid Committing

The `.gitignore` prevents the following from being committed:

| Path            | Reason                              |
|-----------------|-------------------------------------|
| `/vendor/`      | Composer dependencies (install via `composer install`) |
| `/.env`         | Environment-specific configuration  |
| `/var/`         | Cache and temporary files           |
| `/node_modules/`| Node.js dependencies                |
| `/public/build/`| Webpack output (rebuild with `npm run dev`) |
| `/.idea/`       | PHPStorm project files              |
| `.vscode/`      | VS Code workspace settings          |
| `.vs/`          | Visual Studio settings              |
