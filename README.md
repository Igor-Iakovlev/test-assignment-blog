# Blog (Test Assignment)

A small blog application built with PHP, Smarty templates, MySQL and Docker.
No frameworks.

## Stack

- PHP 8.5 (fpm-alpine)
- Smarty 5
- MySQL 8.4
- Nginx 1.27
- Sass (compiled in a dedicated `node:24-alpine` container)

## Features

- Home page: each category with at least one post, three latest posts per
  category, link to the full listing.
- Category page: title, description, sortable post list (by date or views),
  pagination.
- Post page: full post with image, view counter (incremented on each visit),
  three related posts based on shared categories.
- Database seeder with deterministic output via a fixed Faker seed.

## Quick start

```sh
cp .env.example .env

# Edit .env if your host UID/GID is not 1000:1000, then:
docker compose up -d --build

# Install PHP dependencies inside the container:
docker compose exec php composer install

# Populate the database with sample data:
docker compose exec php php db/seed.php
```

The site is then available at <http://localhost:8080>.

## Project layout

```
.
├── assets/scss/           SCSS sources (compiled by the scss service)
├── docker/                Dockerfile for PHP and nginx config
├── docker-compose.yml
└── src/
    ├── app/
    │   ├── Controllers/   HomeController, CategoryController, PostController
    │   ├── Core/          Router, Controller, View, Database
    │   ├── Exceptions/
    │   ├── Models/        Category, Post
    │   └── Support/       Paginator
    ├── db/
    │   ├── schema.sql     Loaded into MySQL on first container start
    │   └── seed.php       CLI seeder
    ├── public/
    │   ├── css/           Compiled CSS (gitignored)
    │   └── index.php      Front controller
    └── templates/         Smarty templates
```

## Seeder

```sh
# Add the default set (5 categories, 50 posts) on top of existing data:
docker compose exec php php db/seed.php

# Wipe the tables and seed from scratch:
docker compose exec php php db/seed.php --reset

# Add a specific amount:
docker compose exec php php db/seed.php --posts=20
docker compose exec php php db/seed.php --categories=3 --posts=10

# Show full help:
docker compose exec php php db/seed.php --help
```

The seeder uses a fixed Faker seed so consecutive runs produce identical data.

## Code style

The codebase follows the `@PER-CS` preset. To re-apply it:

```sh
docker run --rm -v "$(pwd):/code" -w /code --user "$(id -u):$(id -g)" \
    ghcr.io/php-cs-fixer/php-cs-fixer:3-php8.3 fix
```

Configuration lives in `.php-cs-fixer.php`.

## AI usage

AI assistance was used for Docker boilerplate, scaffolding, seed data
generation, documentation, and discussing trade-offs (e.g. plain SQL vs
query builder, seeder strategy). Implementation, SQL queries, and
templates were written and reviewed manually.
