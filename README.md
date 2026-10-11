# Manucurist — Nail Polish Stock Tracker

A small Symfony service to browse nail polish stock, filter by shade, spot products running low, and record sales.

## Stack

- PHP / Symfony
- Doctrine ORM + migrations + fixtures
- Twig + Bootstrap 5 (CDN)
- PHPUnit
- Docker

## Getting started

```bash
git clone <repo-url> manucurist-stock
cd manucurist-stock

docker compose up -d --build

# Install dependencies, create the schema, load the 10 products from the test data
docker compose exec php composer install
docker compose exec php bin/console doctrine:migrations:migrate --no-interaction
docker compose exec php bin/console doctrine:fixtures:load --no-interaction
```

The app is then available at <http://localhost:8080/stock>.


## Run the tests

```bash
docker compose exec php bin/phpunit
```

Unit tests cover the stock rules on `Product` (see below).

## Features

- **List products** with name, shade, price (excl. VAT), stock, alert threshold and status.
- **Filter by shade** (`?shade=red`, partial match).
- **Low-stock filter** (`?alert=1`).
- **Record a sale** (`POST /stock/{id}/sell`) with quantity validation.

## Business rules

- A product is **low on stock** when `stock < alertThreshold`. 
- A product with `stock = 0` is shown as out of stock and its sell button is disabled.
- A sale **cannot exceed the available stock**: `Product::sell()` throws an `InsufficientStockException` and the stock is left untouched.
- The quantity must be a positive integer.

## Design choices

- **Doctrine + fixtures instead of reading the JSON at runtime.** The JSON is only a seed. Stock changes must be persisted, so a database is the natural source of truth.
- **Business logic lives in the entity.** `Product::sell()` keep the controller thin and make the rules unit-testable without a database.
- **Dedicated domain exception** (`InsufficientStockException`) so the controller can turn a business error into a user-friendly message.
- **Optimistic locking** (`#[ORM\Version]`). Two concurrent sales could both read the same stock and oversell. With a version column, the second flush fails with an `OptimisticLockException` and the user is asked to retry.
- **CSRF protection** on the sell form, and POST → redirect → GET to avoid duplicate submissions on refresh.

## Known limitations / next steps

- No pagination on the product list (fine for 10 products, needed for a real catalogue).
- No authentication or roles: anyone can record a sale.
- Filters are lost after recording a sale (redirect goes back to the unfiltered list).
- No stock movement history: only the current stock is stored. An audit table of sales would be the next step.
- No functional (HTTP-level) tests, only unit tests on the domain logic.
- Bootstrap is loaded from a CDN without SRI; AssetMapper would be cleaner for production.