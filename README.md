# Simple MVC PHP Framework

This is an simple MVC framework written in PHP language.

## Features
- Extensive error handling using whoops framework.
- Twig templating language.
- Enabled Dependency Injection using Symfony Dependency Injection component.

## Installed Software in Docker Container
PHP 8 \
Mariadb 10.2.29 \
Nginx 1.21.6

## Downloaded Packages
Twig \
whoops \
Symfony Routing \
Symfony HttpFoundation \
Symfony dotenv \
Phinx 
Monolog

## Dev Tools
PHPStan \
Symfony varDumper 

## Setup
To get it working, follow these steps:

**Build and Run Container Image**

To build container image, in the Windows Terminal inside your project root run: 
```
$ docker compose up -d --build
```
The --build flag only needs one time to build the images. After that each time you use command
`docker-compose up -d` to start the container.

**Configure .env File**

Create a `.env` file and copy the content from `.env.dist` file.

**Download Composer dependencies**

```
$ docker exec -it micro-mvc-php-container composer install
```

**Run the site**

As mentioned in docker-compose.yml now check out the site at `http://localhost:10320`
